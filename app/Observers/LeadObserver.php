<?php

namespace App\Observers;

use App\Models\Lead;

class LeadObserver
{
    public function saving(Lead $lead)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $lead->company_id = company()->id;
        }
    }
}
