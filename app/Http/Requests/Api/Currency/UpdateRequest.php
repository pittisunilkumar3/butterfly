<?php

namespace App\Http\Requests\Api\Currency;

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
			'symbol'    => 'required',
			'position'    => 'required|in:front,behind',
			'code'    => 'required',
		];

		return $rules;
	}
}