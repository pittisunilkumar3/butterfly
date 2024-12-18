<?php

namespace App\Http\Requests\Api\Lead;

use App\Http\Requests\Api\BaseRequest;

class SendEmailRequest extends BaseRequest
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
            'email_template_id' => 'required',
            'lead_id' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ];

        return $rules;
    }
}
