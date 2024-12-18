<?php

namespace App\Http\Requests\Api\Salesman;

use Illuminate\Validation\Rule;
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
            'phone'    => [
                'numeric',
                Rule::unique('users', 'phone')->where(function ($query) {
                    return $query->where('user_type', 'salesman')
                        ->orWhere('user_type', 'super_admins');
                })
            ],
            'name' => 'required',
            'email'    => [
                'required', 'email',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('user_type', 'salesman')
                        ->orWhere('user_type', 'super_admins');
                })
            ],
            'status' => 'required',
            'password' => 'required|min:8',
        ];

        return $rules;
    }
}
