<?php

namespace App\Http\Requests\Api\Lead;

use App\Http\Requests\Api\BaseRequest;

class CreateLeadRequest extends BaseRequest
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
            'campaign_id' => 'required'
        ];

        // TODO - condition according to required Type


        return $rules;
    }
}
