<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\LeadFollowUp\DeleteRequest;
use App\Http\Requests\Api\LeadFollowUp\FollowUpActionRequest;
use App\Http\Requests\Api\LeadFollowUp\IndexRequest;
use App\Models\Lead;
use App\Models\LeadLog;
use Examyou\RestAPI\ApiResponse;

class LeadFollowUpController extends ApiBaseController
{
    protected $model = Lead::class;

    protected $indexRequest = IndexRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();
        $request = request();

        $query = $query->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->join('lead_logs', 'leads.lead_follow_up_id', '=', 'lead_logs.id')
            ->where('campaigns.status', '!=', 'completed')
            ->whereNotNull('leads.lead_follow_up_id');

        // For user filter
        if ($user->ability('admin', 'leads_view_all') && $request->has('user_id') && $request->user_id != "") {
            $userId = $this->getIdFromHash($request->user_id);
            $query = $query->where('lead_logs.user_id', '=', $userId);
        } else if (!$user->ability('admin', 'leads_view_all')) {
            $query = $query->where('lead_logs.user_id', '=', $user->id);
        }

        if ($request->has('campaign_id') && $request->campaign_id != "") {
            $campaignId = $this->getIdFromHash($request->campaign_id);

            $query = $query->where('leads.campaign_id', $campaignId);
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

    public function delete(DeleteRequest $request)
    {
        $xLeadId = $request->x_lead_id;
        $leadId = $this->getIdFromHash($xLeadId);
        $bookingType = $request->booking_type;

        Common::updateBookingStatus($bookingType, $leadId, 'deleted');

        return ApiResponse::make('Success', []);
    }

    public function takeFollowUpAction(FollowUpActionRequest $request)
    {
        $xLeadId = $request->x_lead_id;
        $leadId = $this->getIdFromHash($xLeadId);
        $bookingType = $request->booking_type;

        Common::updateBookingStatus($bookingType, $leadId, 'actioned');

        return ApiResponse::make('Success', [
            'lead_id' => $xLeadId,
        ]);
    }
}
