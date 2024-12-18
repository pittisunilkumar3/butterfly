<?php

namespace App\Http\Requests\Api\Company;

use App\Http\Requests\Api\BaseRequest;

class UpdateRequest extends BaseRequest
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
            'name'    => 'required',
            'short_name'    => 'required',
            'email'    => 'required|email',
            'phone'    => 'required|integer',
            'timezone' => 'required',
            'date_format' => 'required',
            'time_format' => 'required',
            'currency_id' => 'required',
            'lang_id' => 'required',
        ];

        return $rules;
    }
}
