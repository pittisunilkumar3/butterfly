<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadConversation;
use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $whatsappNumber;
    protected $messagingServiceSid;

    public function __construct()
    {
        try {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            $this->whatsappNumber = config('services.twilio.whatsapp_number');
            
            // Debug Twilio configuration
            \Log::debug('Twilio Configuration:', [
                'account_sid' => $accountSid,
                'whatsapp_number' => $this->whatsappNumber,
                'auth_token_exists' => !empty($authToken),
                'auth_token_length' => strlen($authToken)
            ]);

            if (empty($accountSid) || empty($authToken) || empty($this->whatsappNumber)) {
                throw new \Exception('Twilio configuration is incomplete. Please check your .env file.');
            }

            $this->client = new Client($accountSid, $authToken);
        } catch (\Exception $e) {
            \Log::error('Twilio Init Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function sendWelcomeMessage($phoneNumber, $campaignName)
    {
        \Log::info('Sending welcome message:', [
            'phone' => $phoneNumber,
            'campaign' => $campaignName
        ]);
        $message = "Hello! 😊 I'm Sarah from LoanWise. Are you interested in a Personal Loan or Business Loan?";
        return $this->sendMessage($phoneNumber, $message);
    }

    protected function formatPhoneNumber($phoneNumber) 
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // If number starts with '91' without '+', add it
        if (preg_match('/^91\d{10}$/', $phone)) {
            $phone = '+' . $phone;
        }
        // If number is just 10 digits, add +91
        else if (preg_match('/^\d{10}$/', $phone)) {
            $phone = '+91' . $phone;
        }
        // If number starts with + but no country code, add 91
        else if (preg_match('/^\+\d{10}$/', $phone)) {
            $phone = '+91' . substr($phone, 1);
        }
        // If no + prefix but has country code, add +
        else if (preg_match('/^91\d+/', $phone)) {
            $phone = '+' . $phone;
        }
        
        \Log::debug('Phone number formatting:', [
            'input' => $phoneNumber,
            'formatted' => $phone,
            'matches' => [
                'is_10_digits' => preg_match('/^\d{10}$/', $phoneNumber),
                'starts_with_91' => preg_match('/^91\d+/', $phoneNumber),
                'starts_with_plus' => strpos($phoneNumber, '+') === 0
            ]
        ]);
        
        return $phone;
    }

    public function sendMessage($to, $message)
    {
        try {
            // Format the phone number
            $to = $this->formatPhoneNumber($to);
            
            // Format WhatsApp numbers
            $whatsappTo = 'whatsapp:' . $to;
            $whatsappFrom = 'whatsapp:' . $this->whatsappNumber;

            \Log::debug('Attempting to send WhatsApp message:', [
                'to' => $whatsappTo,
                'from' => $whatsappFrom,
                'message' => $message,
                'raw_to' => $to,
                'raw_from' => $this->whatsappNumber,
                'client_exists' => isset($this->client),
                'client_type' => get_class($this->client)
            ]);

            try {
                $result = $this->client->messages->create(
                    $whatsappTo,
                    [
                        'from' => $whatsappFrom,
                        'body' => $message
                    ]
                );

                \Log::info('WhatsApp message sent:', [
                    'sid' => $result->sid,
                    'status' => $result->status,
                    'error_code' => $result->errorCode,
                    'error_message' => $result->errorMessage,
                    'direction' => $result->direction,
                    'date_created' => $result->dateCreated,
                    'date_sent' => $result->dateSent
                ]);

                // Find lead by phone number
                $lead = Lead::whereRaw("LOWER(JSON_EXTRACT(lead_data, '$[*].field_value')) LIKE ?", ['%' . substr($to, -10) . '%'])->first();
                
                if ($lead) {
                    // Store the message in database
                    LeadConversation::create([
                        'lead_id' => $lead->id,
                        'message_sid' => $result->sid,
                        'direction' => 'outbound',
                        'message' => $message,
                        'status' => $result->status,
                        'from' => $whatsappFrom,
                        'to' => $whatsappTo
                    ]);
                }

                return [
                    'success' => true,
                    'sid' => $result->sid,
                    'status' => $result->status,
                    'details' => [
                        'direction' => $result->direction,
                        'date_created' => $result->dateCreated,
                        'date_sent' => $result->dateSent
                    ]
                ];

            } catch (\Twilio\Exceptions\RestException $e) {
                \Log::error('Twilio REST Exception:', [
                    'code' => $e->getCode(),
                    'status' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                    'more_info' => $e->getMoreInfo()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ];
            
            \Log::error('Twilio WhatsApp Error:', $error);
            
            return [
                'success' => false,
                'error' => $error
            ];
        }
    }

    public function testMessage($to)
    {
        $message = "Hello! This is a test message from Leads Pro. Your WhatsApp number is: " . $to;
        $result = $this->sendMessage($to, $message);
        
        \Log::debug('Test Message Result:', $result);
        
        return $result;
    }
}
