<?php

namespace App\Http\Requests\Api\Campaign;

use App\Http\Requests\Api\BaseRequest;

class StopCampaignRequest extends BaseRequest
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
            'x_campaign_id'    => 'required',
        ];

        return $rules;
    }
}
