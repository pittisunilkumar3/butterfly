<?php

namespace App\Observers;

use App\Models\EmailTemplate;

class EmailTemplateObserver
{
    public function saving(EmailTemplate $emailTemplate)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $emailTemplate->company_id = company()->id;
        }
    }
}
