<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadConversation;
use App\Services\OpenRouterService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TwilioWebhookController extends Controller
{
    private $openRouterService;
    private $twilioService;

    public function __construct(OpenRouterService $openRouterService, TwilioService $twilioService)
    {
        $this->openRouterService = $openRouterService;
        $this->twilioService = $twilioService;
    }

    public function messageStatus(Request $request)
    {
        $messageSid = $request->MessageSid;
        $messageStatus = $request->MessageStatus;
        $to = $request->To;
        $from = $request->From;
        
        \Log::info('Twilio Message Status Update:', [
            'message_sid' => $messageSid,
            'message_status' => $messageStatus,
            'to' => $to,
            'from' => $from,
            'error_code' => $request->ErrorCode,
            'error_message' => $request->ErrorMessage,
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        // Update message status in database
        $conversation = LeadConversation::where('message_sid', $messageSid)->first();
        if ($conversation) {
            $conversation->status = $messageStatus;
            
            if ($messageStatus === 'delivered') {
                $conversation->delivered_at = Carbon::now();
            } elseif ($messageStatus === 'read') {
                $conversation->read_at = Carbon::now();
            }
            
            $conversation->save();
        }

        return response()->json(['success' => true]);
    }

    public function incomingMessage(Request $request)
    {
        try {
            // Enable query logging
            \DB::enableQueryLog();
            
            $messageSid = $request->MessageSid;
            $from = $request->From;
            $to = $request->To;
            $body = $request->Body;
            $numMedia = $request->NumMedia;
            
            // Debug log - Request received
            \Log::debug('Twilio Webhook Request Received', [
                'message_sid' => $messageSid,
                'from' => $from,
                'to' => $to,
                'body' => $body,
                'all_data' => $request->all()
            ]);

            // Extract phone number from whatsapp:+1234567890 format
            $phoneNumber = substr($from, strpos($from, ':') + 1);
            $lastTenDigits = substr($phoneNumber, -10);
            
            // Debug log - Phone number extraction
            \Log::debug('Phone Number Extraction', [
                'original' => $from,
                'extracted' => $phoneNumber,
                'last_ten' => $lastTenDigits
            ]);

            // Find lead by phone number - try multiple formats
            $possibleFormats = [
                $lastTenDigits,                    // Just the last 10 digits
                '+' . $lastTenDigits,              // +1234567890
                $phoneNumber,                      // Full number as received
                str_replace('+', '', $phoneNumber), // Remove + if present
                '91' . $lastTenDigits,             // Country code without +
                '+91' . $lastTenDigits,            // Country code with +
            ];

            // Debug possible formats we're searching for
            \Log::debug('Searching for phone number formats', [
                'formats' => $possibleFormats
            ]);

            $lead = Lead::where(function($query) use ($possibleFormats) {
                foreach ($possibleFormats as $format) {
                    $query->orWhereRaw("JSON_SEARCH(lead_data, 'one', ?) IS NOT NULL", [$format]);
                }
            })->first();

            // If still not found, try a more flexible search
            if (!$lead) {
                \Log::debug('Trying flexible phone number search');
                $lead = Lead::whereRaw("JSON_SEARCH(lead_data, 'one', ?) IS NOT NULL", ['%' . $lastTenDigits . '%'])->first();
            }

            // Debug the actual SQL query being executed
            $queryLog = \DB::getQueryLog();
            \Log::debug('Lead Search Query', [
                'query' => end($queryLog)
            ]);

            // Debug log - Lead search result
            \Log::debug('Lead Search Result', [
                'found' => !is_null($lead),
                'lead_id' => $lead ? $lead->id : null,
                'phone_searched' => $phoneNumber,
                'last_ten_digits' => $lastTenDigits,
                'lead_data' => $lead ? $lead->lead_data : null
            ]);

            if ($lead) {
                try {
                    // Debug log - Before creating conversation
                    \Log::debug('Attempting to create conversation', [
                        'lead_id' => $lead->id,
                        'message_sid' => $messageSid
                    ]);

                    // Store the incoming message
                    $inboundConversation = LeadConversation::create([
                        'lead_id' => $lead->id,
                        'message_sid' => $messageSid,
                        'direction' => 'inbound',
                        'message' => $body,
                        'status' => 'received',
                        'from' => $from,
                        'to' => $to
                    ]);

                    \Log::debug('Stored inbound message', [
                        'conversation_id' => $inboundConversation->id
                    ]);

                    // Get recent conversation history
                    $recentConversations = LeadConversation::where('lead_id', $lead->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get()
                        ->map(function ($conv) {
                            return [
                                'role' => $conv->direction === 'inbound' ? 'user' : 'assistant',
                                'content' => $conv->message
                            ];
                        })
                        ->toArray();

                    // Generate AI response
                    \Log::debug('Generating AI response', [
                        'user_message' => $body,
                        'conversation_count' => count($recentConversations)
                    ]);
                    
                    $aiResponse = $this->openRouterService->generateResponse($body, $recentConversations);

                    // Send response via Twilio
                    \Log::debug('Sending response via Twilio', [
                        'response' => $aiResponse
                    ]);
                    
                    try {
                        $outboundMessageSid = $this->twilioService->sendMessage($from, $aiResponse);

                        // Store the AI response
                        $outboundConversation = LeadConversation::create([
                            'lead_id' => $lead->id,
                            'message_sid' => $outboundMessageSid,
                            'direction' => 'outbound',
                            'message' => $aiResponse,
                            'status' => 'sent',
                            'from' => $to,
                            'to' => $from
                        ]);

                        \Log::debug('Stored outbound message', [
                            'conversation_id' => $outboundConversation->id,
                            'message_sid' => $outboundMessageSid
                        ]);

                    } catch (\Exception $e) {
                        \Log::error('Failed to store outbound message', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'lead_id' => $lead->id,
                            'message' => $aiResponse
                        ]);
                    }

                    \Log::debug('Response sent successfully', [
                        'message_sid' => $outboundMessageSid ?? 'Unknown'
                    ]);

                    // Debug log - Conversation created
                    \Log::debug('Conversation created successfully', [
                        'conversation_id' => $inboundConversation->id,
                        'lead_id' => $lead->id
                    ]);

                } catch (\Exception $e) {
                    // Debug log - Creation error
                    \Log::error('Failed to create conversation', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'lead_id' => $lead->id,
                        'message_sid' => $messageSid
                    ]);
                    throw $e;
                }
            } else {
                // Debug log - No lead found
                \Log::warning('No lead found for incoming message', [
                    'phone_number' => $phoneNumber,
                    'message_sid' => $messageSid,
                    'last_ten_digits' => $lastTenDigits
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            // Debug log - Critical error
            \Log::error('Critical error in webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
