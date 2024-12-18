<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\LeadLog\IndexRequest;
use App\Http\Requests\Api\LeadLog\StoreRequest;
use App\Http\Requests\Api\LeadLog\UpdateRequest;
use App\Http\Requests\Api\LeadLog\DeleteRequest;
use App\Models\LeadLog;
use Carbon\Carbon;
use Examyou\RestAPI\Exceptions\ApiException;

class LeadLogController extends ApiBaseController
{
    protected $model = LeadLog::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();
        $request = request();

        $query = $query->join('leads', 'leads.id', '=', 'lead_logs.lead_id')
            ->join('campaigns', 'campaigns.id', 'leads.campaign_id');

        if (!$user->ability('admin', 'leads_view_all')) {
            if ($request->has('log_type') && $request->log_type == 'salesman_bookings') {
                $query = $query->where('lead_logs.created_by_id', $user->id);
            } else if (
                $request->has('log_type') && $request->has('page_name') &&
                $request->page_name == 'lead_action' &&
                $request->has('lead_id') && $request->lead_id != "" &&
                ($request->log_type == 'call_log' || $request->log_type == 'notes' || $request->log_type == 'all')
            ) {
                // When request comes from TakeLeadActionPage
            } else {

                $query = $query->where('lead_logs.user_id', $user->id);
            }
        }

        // For Lead Follow Up showing
        // only not actioned leads
        if ($request->has('log_type') && $request->log_type == 'lead_follow_up') {
            $query = $query->where('booking_status', '!=', 'actioned');
        }

        // Extra Filters
        if ($user->ability('admin', 'view_completed_campaigns')) {
            if ($request->has('campaign_status') && $request->campaign_status == "completed") {
                $query = $query->where('campaigns.status', '=', 'completed');
            } else {
                $query = $query->where('campaigns.status', '!=', 'completed');
            }
        } else {
            $query = $query->where('campaigns.status', '!=', 'completed');
        }

        if ($request->has('lead_id') && $request->lead_id != "") {
            $leadId = $this->getIdFromHash($request->lead_id);

            $query = $query->where('lead_logs.lead_id', $leadId);
        }

        if ($request->has('campaign_id') && $request->campaign_id != "") {
            $campaignId = $this->getIdFromHash($request->campaign_id);

            $query = $query->where('leads.campaign_id', $campaignId);
        }

        if ($request->has('log_type') && $request->log_type == 'call_log') {
            $query = $query->where('lead_logs.time_taken', '>', 0);
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('lead_logs.date_time >= ?', [$startDate])
                ->whereRaw('lead_logs.date_time <= ?', [$endDate]);
        }

        return $query;
    }

    public function storing($leadLog)
    {
        $loggedUser = user();
        $request = request();

        if ($request->log_type == '' || $request->log_type != 'notes') {
            throw new ApiException("Not Allowed");
        }

        $leadLog->user_id = $loggedUser->id;
        $leadLog->date_time = Carbon::now();

        return $leadLog;
    }

    public function updating($leadLog)
    {
        $request = request();

        if ($request->has('user_id') || $request->has('date_time') || $request->log_type == '' || $request->log_type != 'notes') {
            throw new ApiException("Not Allowed");
        }

        return $leadLog;
    }
}
