<?php

namespace App\Http\Requests\Api\LeadLog;

use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'log_type' => 'required|in:notes',
        ];

        if ($this->log_type == 'notes') {
            $rules['lead_id'] = 'required';
            $rules['notes'] = 'required';
        }

        return $rules;
    }
}
