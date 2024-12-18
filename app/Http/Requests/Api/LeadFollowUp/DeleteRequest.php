<?php

namespace App\Http\Requests\Api\LeadFollowUp;

use App\Http\Requests\Api\BaseRequest;

class DeleteRequest extends BaseRequest
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
        return [
            'x_lead_id' => 'required',
            'booking_type' => 'required|in:lead_follow_up',
        ];
    }
}
