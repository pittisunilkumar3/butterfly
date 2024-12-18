<?php

namespace App\Http\Requests\Api\Product;

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
        return [
            'name' => 'required',
            'product_type' => 'required',
            'price' => 'required|numeric',
            'tax_rate' => 'required',
            'tax_label' => 'required',
        ];
    }
}
