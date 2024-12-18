<?php

namespace App\Http\Requests\Api\Role;

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
			'name' => 'required|not_in:admin|unique:roles,name',
			'display_name' => 'required',
			'permissions' => 'required'
		];

		return $rules;
	}
}
