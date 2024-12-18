<?php

namespace App\Observers;

use App\Models\Campaign;

class CampaignObserver
{
    public function saving(Campaign $campaign)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $campaign->company_id = company()->id;
        }
    }
}
