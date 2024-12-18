<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\CallManager\IndexRequest;
use App\Models\Campaign;

class CallManagerController extends ApiBaseController
{
    protected $model = Campaign::class;

    protected $indexRequest = IndexRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();
        $request = request();

        if (!$user->ability('admin', 'campaigns_view_all') || !$request->has('view_type') || ($request->has('view_type') && $request->view_type == 'self')) {
            $query = $query->join('campaign_users', 'campaign_users.campaign_id', '=', 'campaigns.id')
                ->where('campaign_users.user_id', $user->id);
        }

        // Filter By Campaign Status
        if ($user->ability('admin', 'view_completed_campaigns')) {
            if ($request->has('campaign_status') && $request->campaign_status == "completed") {
                $query = $query->where('status', '=', 'completed');
            } else {
                $query = $query->where('status', '!=', 'completed');
            }
        } else {
            $query = $query->where('status', '!=', 'completed');
        }


        return $query;
    }
}
