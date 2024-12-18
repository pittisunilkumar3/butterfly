<?php

namespace App\Http\Requests\Api\FormFieldName;

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
            'field_name' => 'required',
            'similar_field_names'  => 'required',
            'visible' => 'required',
        ];

        return $rules;
    }
}
