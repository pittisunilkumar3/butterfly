<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Lead\IndexRequest;
use App\Http\Requests\Api\Lead\StoreRequest;
use App\Http\Requests\Api\Lead\UpdateRequest;
use App\Http\Requests\Api\Lead\DeleteRequest;
use App\Http\Requests\Api\Lead\CreateLeadRequest;
use App\Http\Requests\Api\Lead\CreateBookingRequest;
use App\Http\Requests\Api\Lead\SendEmailRequest;
use App\Http\Requests\Api\Lead\StartFollowRequest;
use App\Models\Campaign;
use App\Models\CampaignUser;
use App\Models\Lead;
use App\Models\LeadLog;
use App\Models\Salesman;
use App\Models\Settings;
use App\Models\LeadConversation;
use App\Notifications\SendLeadMail;
use App\Scopes\CompanyScope;
use App\Services\ConversationAnalyzer;
use App\Services\TwilioService;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

class LeadController extends ApiBaseController
{
    protected $model = Lead::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $request = request();
        $user = user();

        // Extra Filter For campaign Type
        $query = $query->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id');


        if ($user->ability('admin', 'view_completed_campaigns')) {
            // Filter By Campaign Status
            if ($request->has('campaign_status') && $request->campaign_status == "completed") {
                $query = $query->where('status', '=', 'completed');
            } else {
                $query = $query->where('status', '!=', 'completed');
            }
        } else {
            $query = $query->where('status', '!=', 'completed');
        }

        // Extra Filters
        if ($request->has('lead_field_name') && $request->lead_field_name != "" && $request->has('lead_field_value') && $request->lead_field_value != "") {
            $filterStringOne = 'field_name":"' . $request->lead_field_name . '","field_value":"' . $request->lead_field_value;
            $filterStringTwo = "field_name':'" . $request->lead_field_name . "','field_value':'" . $request->lead_field_value;
            $query = $query->where('lead_data', 'like', '%' . $filterStringOne . '%')
                ->orWhere('lead_data', 'like', '%' . $filterStringTwo . '%');
        }

        if ($request->has('lead_status') && $request->lead_status != '') {
            $query = $query->where('leads.lead_status', $request->lead_status);
        }

        // Started Filter
        $started = $request->has('started') && $request->started == 'not_started' ? 0 : 1;
        $query = $query->where('started', $started);

        // If user either not admin or have leads_view_all permissions
        // then lead last_action_by must be logged in user
        // and only leads started will be visible

        if ($user->ability('admin', 'leads_view_all')) {
            if ($started) {
                $userId = $request->has('user_id') && $request->user_id != "" ? $request->user_id : $user->id;
                $query = $query->where('leads.last_action_by', $userId);
            }
        } else {
            $query = $query->where('leads.last_action_by', $user->id)
                ->where('leads.started', 1);
        }

        return $query;
    }

    public function createLead(CreateLeadRequest $request)
    {
        $user = user();

        if (!$user->ability('admin', 'leads_create')) {
            throw new ApiException("Not Allowed");
        }

        $xCampaignId = $request->campaign_id;
        $campaignId = $this->getIdFromHash($xCampaignId);
        $loggedUser = user();
        $campaign = Campaign::find($campaignId);

        // Calculating Lead Data Hash
        $leadHashString = "";
        $leadDatas = $request->lead_data;
        
        // Find phone number from lead data
        $phoneNumber = null;
        foreach ($leadDatas as $leadData) {
            $fieldName = strtolower($leadData['field_name']);
            if (in_array($fieldName, ['phone', 'phone number', 'phone no', 'Phone No', 'mobile', 'contact', 'whatsapp'])) {
                $phoneNumber = $leadData['field_value'];
                
                // Log to Laravel log file
                \Log::info('Found phone number in lead data:', [
                    'field_name' => $leadData['field_name'],
                    'phone_number' => $phoneNumber
                ]);
                
                // Log to browser console
                $logData = json_encode([
                    'field_name' => $leadData['field_name'],
                    'phone_number' => $phoneNumber
                ]);
                echo "<script>console.log('Found phone number:', $logData);</script>";
            }
            $leadHashString .= strtolower($leadData['field_value']);
        }

        $lead = new Lead();
        $lead->campaign_id = $campaignId;

        // Reference Prefix
        if ($campaign->allow_reference_prefix) {
            $lead->reference_number = $campaign->reference_prefix . Carbon::now()->timestamp;
        }

        $lead->lead_data = $request->lead_data;
        $lead->created_by = $loggedUser->id;
        $lead->lead_hash = md5($leadHashString . $campaignId);
        $lead->save();

        // Send welcome message if phone number exists
        if ($phoneNumber) {
            try {
                $twilioService = new TwilioService();
                $result = $twilioService->sendWelcomeMessage($phoneNumber, $campaign->name);
                
                // Log success to both Laravel log and browser console
                $logData = [
                    'lead_id' => $lead->id,
                    'campaign' => $campaign->name,
                    'phone' => $phoneNumber,
                    'result' => $result
                ];
                \Log::info('Welcome message sent for new lead:', $logData);
                echo "<script>console.log('Welcome message sent:', " . json_encode($logData) . ");</script>";
                
            } catch (\Exception $e) {
                // Log error to both Laravel log and browser console
                $errorData = [
                    'lead_id' => $lead->id,
                    'campaign' => $campaign->name,
                    'phone' => $phoneNumber,
                    'error' => $e->getMessage()
                ];
                \Log::error('Failed to send welcome message:', $errorData);
                echo "<script>console.error('Failed to send message:', " . json_encode($errorData) . ");</script>";
            }
        } else {
            // Log no phone number found
            $logData = [
                'lead_id' => $lead->id,
                'campaign' => $campaign->name,
                'lead_data' => $leadDatas
            ];
            \Log::info('No phone number found for lead:', $logData);
            echo "<script>console.warn('No phone number found:', " . json_encode($logData) . ");</script>";
        }

        // Calculating Lead Counts
        Common::recalculateCampaignLeads($campaignId);

        return ApiResponse::make('Success', []);
    }

    public function createLeadCallLog($leadXId)
    {
        $id = $this->getIdFromHash($leadXId);
        $loggedUser = user();

        $lead = Lead::find($id);

        // Recalculate Time Taken in Lead
        // And insert it in lead
        $recalculateLeadTime = LeadLog::where('lead_id', $lead->id)
            // ->where('user_id', '=', $loggedUser->id)
            ->where('log_type', '=', 'call_log')
            ->sum('time_taken');
        $lead->time_taken = $recalculateLeadTime;
        $lead->save();

        // TODO - Check if any other user is not attending this lead
        $callLog = new LeadLog();
        $callLog->lead_id = $lead->id;
        $callLog->log_type = 'call_log';
        $callLog->user_id = $loggedUser->id;
        $callLog->started_on = (int)$lead->time_taken;
        $callLog->time_taken = 0;
        $callLog->date_time = Carbon::now();
        $callLog->save();

        $leadNumber = Lead::where('id', '<=', $lead->id)
            ->where('campaign_id', $lead->campaign_id)
            ->count();


        return ApiResponse::make('Success', [
            'call_log' => $callLog,
            'lead_number' => $leadNumber
        ]);
    }

    public function createBooking(CreateBookingRequest $request)
    {
        $request = request();
        $bookingType = $request->booking_type;
        $leadXId = $request->lead_id;
        $id = $this->getIdFromHash($leadXId);

        $lead = Lead::find($id);

        // TODO - Check if any other user is not attending this lead

        $bookingId = $bookingType == 'lead_follow_up' ? $lead->lead_follow_up_id : $lead->salesman_booking_id;

        if (!is_null($bookingId)) {
            $booking = LeadLog::where('log_type', $bookingType)
                ->where('id', $bookingId)
                ->first();
        }


        if (is_null($bookingId) || (!is_null($bookingId) && !$booking)) {
            $booking = new LeadLog();
            $booking->lead_id = $lead->id;
            $booking->log_type = $bookingType;
        }

        $booking->date_time = $request->date_time;
        $booking->user_id = $request->user_id;
        $booking->notes = $request->notes;
        $booking->save();

        if ($bookingType == 'lead_follow_up') {
            $lead->lead_follow_up_id = $booking->id;
        } else {
            $lead->salesman_booking_id = $booking->id;
        }
        $lead->save();

        $bookingData = LeadLog::select('id', 'date_time', 'user_id')
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->find($booking->id);

        return ApiResponse::make('Success', [
            'booking' => $bookingData
        ]);
    }

    public function leadCampaignMembers()
    {
        $request = request();
        $bookingType = $request->booking_type;
        $leadXId = $request->lead_id;
        $id = $this->getIdFromHash($leadXId);

        $lead = Lead::select('id', 'campaign_id')->find($id);

        // TODO - Check if any other user is not attending this lead

        $users = [];

        if ($bookingType == 'lead_follow_up') {
            $users = CampaignUser::select('users.id', 'users.name')
                ->join('users', 'users.id', '=', 'campaign_users.user_id')
                ->where('campaign_users.campaign_id', $lead->campaign_id)
                ->get();
        } else if ($bookingType == 'salesman_bookings') {
            $users = Salesman::select('id', 'name')->get();
        }

        return ApiResponse::make('Success', [
            'users' => $users
        ]);
    }

    public function startFollowUp(StartFollowRequest $request)
    {
        $request = request();
        $bookingType = $request->log_type;
        $leadXId = $request->x_lead_id;
        $id = $this->getIdFromHash($leadXId);

        $lead = Lead::find($id);

        // TODO - Check if any other user is not attending this lead

        $booking = LeadLog::where('log_type', $bookingType)
            ->where('lead_id', $lead->id)
            ->first();

        if (!$booking) {
            $booking = new LeadLog();
            $booking->lead_id = $lead->id;
            $booking->log_type = $bookingType;
        }

        $booking->date_time = $request->date_time;
        $booking->user_id = $request->user_id;
        $booking->notes = $request->notes;
        $booking->save();

        $bookingData = LeadLog::select('id', 'date_time', 'user_id')
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->find($booking->id);

        return ApiResponse::make('Success', []);
    }

    public function leadCampaignStats()
    {
        $request = request();
        $user = user();

        // Total Active/Completed Campaign Counts
        $totalActiveCampaign = Campaign::where('campaigns.status', '!=', 'completed');
        $totalCompletedCampaign = Campaign::where('campaigns.status', '=', 'completed');

        // Total Leads
        $totalLeads = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id');
        $callMade = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->where('started', 1);
        $totalDuration = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id');


        if (!$user->ability('admin', 'leads_view_all')) {
            $totalActiveCampaign = $totalActiveCampaign->join('campaign_users', 'campaign_users.campaign_id', '=', 'campaigns.id')
                ->where('campaign_users.user_id', $user->id);
            $totalCompletedCampaign = $totalCompletedCampaign->join('campaign_users', 'campaign_users.campaign_id', '=', 'campaigns.id')
                ->where('campaign_users.user_id', $user->id);

            $callMade = $callMade->where('leads.last_action_by', $user->id);
            $totalDuration = $totalDuration->where('leads.last_action_by', $user->id);
        }

        if ($request->has('campaign_status') && $request->campaign_status == 'completed') {
            $totalLeads = $totalLeads->where('campaigns.status', '=', 'completed');
            $callMade = $callMade->where('campaigns.status', '=', 'completed');
            $totalDuration = $totalDuration->where('campaigns.status', '=', 'completed');
        } else {
            $totalLeads = $totalLeads->where('campaigns.status', '!=', 'completed');
            $callMade = $callMade->where('campaigns.status', '!=', 'completed');
            $totalDuration = $totalDuration->where('campaigns.status', '!=', 'completed');
        }

        if ($request->has('campaign_id') && $request->campaign_id != '') {
            $campaignId = $this->getIdFromHash($request->campaign_id);
            $totalLeads = $totalLeads->where('campaigns.id', $campaignId);
            $callMade = $callMade->where('campaigns.id', $campaignId);
            $totalDuration = $totalDuration->where('campaigns.id', $campaignId);
        }


        $totalActiveCampaign = $totalActiveCampaign->count();
        $totalCompletedCampaign = $totalCompletedCampaign->count();
        $totalLeads = $totalLeads->count();
        $callMade = $callMade->count();
        $totalDuration = $totalDuration->sum('time_taken');


        return ApiResponse::make('Success', [
            'total_active_campaign' => $totalActiveCampaign,
            'total_completed_campaign' => $totalCompletedCampaign,
            'total_leads' => $totalLeads,
            'call_made' => $callMade,
            'total_duration' => $totalDuration,
        ]);
    }

    public function sendEmail(SendEmailRequest $request)
    {
        $user = user();
        $email = $request->email;
        $subject = $request->subject;
        $message = $request->message;
        $success = false;
        $xLeadId = $request->lead_id;
        $leadId = $this->getIdFromHash($xLeadId);
        $mailSetting = Settings::withoutGlobalScope(CompanyScope::class)->where('setting_type', 'email')
            ->where('name_key', 'smtp')
            ->first();


        if ($mailSetting && $mailSetting->status && $mailSetting->verified) {
            $mailSent = true;

            try {
                Notification::route('mail', $email)->notify(new SendLeadMail($subject, $message));
            } catch (\Exception $exception) {

                $mailSent = false;
            }

            // TODO - insert mail
            if ($mailSent) {
                $leadLog = new LeadLog();
                $leadLog->lead_id = $leadId;
                $leadLog->log_type = 'email';
                $leadLog->user_id = $user->id;
                $leadLog->date_time = Carbon::now();
                $leadLog->notes = json_encode([
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                ]);
                $leadLog->save();
            }

            $success = true;
        }

        return ApiResponse::make('Success', [
            'success' => $success,
        ]);
    }

    public function getCampaignLeads($campaignXId)
    {
        try {
            // Decode the campaign ID from hash
            $campaignId = $this->getIdFromHash($campaignXId);

            // Find the campaign
            $campaign = Campaign::findOrFail($campaignId);

            // Fetch leads for this specific campaign
            $leads = Lead::where('campaign_id', $campaignId)
                ->with(['firstActioner' => function($query) {
                    $query->select('id', 'xid', 'name');
                }, 'lastActioner' => function($query) {
                    $query->select('id', 'xid', 'name');
                }])
                ->get()
                ->map(function($lead) {
                    // Parse lead_data if it's stored as a string
                    $leadData = is_string($lead->lead_data) 
                        ? json_decode($lead->lead_data, true) 
                        : $lead->lead_data;

                    // Create a flattened lead data structure
                    $dynamicFields = [];
                    if (is_array($leadData)) {
                        foreach ($leadData as $field) {
                            $dynamicFields[$field['field_name'] ?? $field['name'] ?? 'unknown'] = 
                                $field['field_value'] ?? $field['value'] ?? null;
                        }
                    }

                    return [
                        'id' => $lead->id,
                        'xid' => $lead->xid,
                        'campaign_id' => $lead->campaign_id,
                        'name' => $lead->name,
                        'phone' => $lead->phone,
                        'email' => $lead->email,
                        'status' => $lead->lead_status ?? 'unknown',
                        'created_at' => $lead->created_at,
                        'updated_at' => $lead->updated_at,
                        'dynamic_fields' => $dynamicFields,
                        'first_actioner' => $lead->firstActioner ? [
                            'id' => $lead->firstActioner->id,
                            'xid' => $lead->firstActioner->xid,
                            'name' => $lead->firstActioner->name
                        ] : null,
                        'last_actioner' => $lead->lastActioner ? [
                            'id' => $lead->lastActioner->id,
                            'xid' => $lead->lastActioner->xid,
                            'name' => $lead->lastActioner->name
                        ] : null
                    ];
                });

            return ApiResponse::make('Success', [
                'campaign' => [
                    'id' => $campaign->id,
                    'xid' => $campaign->xid,
                    'name' => $campaign->name
                ],
                'leads' => $leads
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching campaign leads: ' . $e->getMessage());
            return ApiResponse::make('Error', ['message' => $e->getMessage()], 500);
        }
    }

    public function testWhatsApp()
    {
        try {
            $phoneNumber = '+916303727148';
            
            \Log::info('Starting WhatsApp test:', [
                'phone_number' => $phoneNumber
            ]);
            
            $twilioService = new TwilioService();
            $result = $twilioService->testMessage($phoneNumber);
            
            \Log::info('WhatsApp test completed:', $result);
            
            return ApiResponse::make('Test completed', $result);
        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ];
            
            \Log::error('WhatsApp test failed:', $error);
            
            return ApiResponse::make('Test failed', $error, 500);
        }
    }

    public function getConversations($id)
    {
        try {
            $lead = Lead::findOrFail($id);
            
            $conversations = LeadConversation::where('lead_id', $id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($conversation) {
                    return [
                        'id' => $conversation->id,
                        'message_sid' => $conversation->message_sid,
                        'direction' => $conversation->direction,
                        'message' => $conversation->message,
                        'status' => $conversation->status,
                        'from' => $conversation->from,
                        'to' => $conversation->to,
                        'delivered_at' => $conversation->delivered_at,
                        'read_at' => $conversation->read_at,
                        'created_at' => $conversation->created_at,
                        'is_from_lead' => $conversation->direction === 'inbound'
                    ];
                });

            return ApiResponse::make('Success', [
                'lead' => $lead,
                'conversations' => $conversations
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching conversations:', [
                'lead_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            throw new ApiException('Failed to fetch conversations');
        }
    }

    public function sendMessage($id, Request $request)
    {
        try {
            $lead = Lead::findOrFail($id);
            $message = $request->input('message');
            
            if (empty($message)) {
                throw new ApiException('Message cannot be empty');
            }

            $phoneNumber = $this->getPhoneNumber($lead);
            if (empty($phoneNumber)) {
                throw new ApiException('Lead does not have a valid phone number');
            }

            // Send message via Twilio
            $twilioService = app(TwilioService::class);
            $messageSid = $twilioService->sendWhatsAppMessage($phoneNumber, $message);

            // Store the message in database
            $conversation = new LeadConversation([
                'lead_id' => $lead->id,
                'message_sid' => $messageSid,
                'direction' => 'outbound',
                'message' => $message,
                'status' => 'sent',
                'from' => config('services.twilio.whatsapp_from'),
                'to' => $phoneNumber
            ]);
            $conversation->save();

            return ApiResponse::make('Message sent successfully', [
                'conversation' => $conversation
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending message:', [
                'lead_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            throw new ApiException('Failed to send message: ' . $e->getMessage());
        }
    }

    private function getPhoneNumber($lead)
    {
        // First try to get from dynamic fields
        if (isset($lead->dynamic_fields['Phone'])) {
            return $lead->dynamic_fields['Phone'];
        }
        if (isset($lead->dynamic_fields['Mobile'])) {
            return $lead->dynamic_fields['Mobile'];
        }
        if (isset($lead->dynamic_fields['WhatsApp'])) {
            return $lead->dynamic_fields['WhatsApp'];
        }

        // Then try phone field if it exists
        if (isset($lead->phone)) {
            return $lead->phone;
        }

        return null;
    }

    public function getConversationAnalysis($id)
    {
        try {
            $lead = Lead::findOrFail($id);
            
            // Log analysis request
            \Log::info('Conversation Analysis Requested', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_phone' => $lead->phone,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            // Get all conversations for this lead
            $conversations = LeadConversation::where('lead_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Log conversation count
            \Log::info('Conversations Retrieved', [
                'lead_id' => $lead->id,
                'conversation_count' => $conversations->count(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            // Format conversations for analysis
            $conversationText = $conversations->map(function($conv) {
                $direction = $conv->direction === 'inbound' ? 'User' : 'Assistant';
                return "$direction: {$conv->message}";
            })->join("\n");

            // Get analysis
            $analyzer = new ConversationAnalyzer();
            $analysis = $analyzer->analyzeConversation($conversationText);

            // Log successful analysis
            \Log::info('Analysis Completed', [
                'lead_id' => $lead->id,
                'score' => $analysis['score'],
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'conversation_score' => $analysis['score'],
                'analysis' => $analysis['summary'] . "\n\n" . $analysis['reasoning']
            ]);

        } catch (\Exception $e) {
            // Log error
            \Log::error('Analysis Request Failed', [
                'lead_id' => $id,
                'error' => $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analyzeConversation(Lead $lead)
    {
        try {
            \Log::info('Conversation analysis requested', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_phone' => $lead->phone
            ]);

            // Get all conversations for the lead
            $conversations = $lead->conversations;
            
            \Log::debug('Retrieved conversations', [
                'lead_id' => $lead->id,
                'conversation_count' => $conversations->count()
            ]);

            if ($conversations->isEmpty()) {
                \Log::warning('No conversations found for analysis', [
                    'lead_id' => $lead->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No conversations found for analysis'
                ], 404);
            }

            // Combine conversations into a single string
            $conversationText = $conversations->map(function ($conv) {
                return "Message: {$conv->message}\nTimestamp: {$conv->created_at}";
            })->join("\n\n");

            // Analyze the conversation
            $analyzer = app(ConversationAnalyzer::class);
            $result = $analyzer->analyzeConversation($conversationText);

            \Log::info('Analysis completed for lead', [
                'lead_id' => $lead->id,
                'score' => $result['score'],
                'summary_length' => strlen($result['summary'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to analyze conversation', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
