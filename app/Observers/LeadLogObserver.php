<?php

namespace App\Observers;

use App\Models\LeadLog;

class LeadLogObserver
{
    public function creating(LeadLog $leadlog)
    {
        $user = user();

        $leadlog->created_by_id = $user->id;
    }

    public function updating(LeadLog $leadlog)
    {
        $user = user();

        $leadlog->updated_by_id = $user->id;
    }

    public function saving(LeadLog $leadlog)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $leadlog->company_id = company()->id;
        }
    }
}
