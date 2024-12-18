<?php

namespace App\Http\Requests\Api\Lead;

use App\Http\Requests\Api\BaseRequest;

class CreateBookingRequest extends BaseRequest
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
            'booking_type' => 'required|in:salesman_bookings,lead_follow_up',
            'lead_id' => 'required',
            'date_time' => 'required',
            'user_id' => 'required',
            'notes' => 'required',
        ];

        // TODO - condition according to required Type


        return $rules;
    }
}
