<?php

namespace App\Http\Requests\Api\Campaign;

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
        $currentStep = $this->current_step;
        $rules = [];

        // Step First
        if ($currentStep == 0) {
            $rules['name'] = 'required';
            $rules['form_id'] = 'required';
            $rules['email_template_id'] = 'required';

            $allUsers = json_decode($this->user_id);
            $totalMembers = count($allUsers);

            if ($totalMembers == 0) {
                $rules['user_id'] = 'required';
            }

            if ($this->allow_reference_prefix == 1 && $this->reference_prefix == '') {
                $rules['reference_prefix'] = 'required';
            }
        }

        return $rules;
    }
}
