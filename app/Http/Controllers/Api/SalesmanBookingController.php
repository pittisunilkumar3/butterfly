<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\SalesmanBooking\DeleteRequest;
use App\Http\Requests\Api\SalesmanBooking\IndexRequest;
use App\Models\Lead;
use Examyou\RestAPI\ApiResponse;

class SalesmanBookingController extends ApiBaseController
{
    protected $model = Lead::class;

    protected $indexRequest = IndexRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();
        $request = request();

        $query = $query->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->join('lead_logs', 'leads.salesman_booking_id', '=', 'lead_logs.id')
            ->where('campaigns.status', '!=', 'completed')
            ->whereNotNull('leads.salesman_booking_id')
            ->where('lead_logs.created_by_id', '=', $user->id);

        // For user filter
        if ($request->has('user_id') && $request->user_id != "") {
            $userId = $this->getIdFromHash($request->user_id);
            $query = $query->where('lead_logs.user_id', '=', $userId);
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
}
