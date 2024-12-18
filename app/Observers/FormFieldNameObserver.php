<?php

namespace App\Observers;

use App\Models\FormFieldName;

class FormFieldNameObserver
{
    public function saving(FormFieldName $formFieldName)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $formFieldName->company_id = company()->id;
        }
    }
}
