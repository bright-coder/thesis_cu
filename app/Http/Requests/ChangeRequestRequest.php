<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangeRequestRequest extends FormRequest
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
            'functionalRequirementNo' => 'required',
            'inputs' => 'required|array|min:1',
            'inputs.*.changeType' => 'required|string|in:add,edit,delete',
            'inputs.*.tableName' => 'required_if:inputs.*.changeType,add|string|min:2',
            'inputs.*.columnName' => 'required_if:inputs.*.changeType,add|string|min:2',

        ];
    }
    
    protected function validationData()
    {
        return $this->json()->all();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['msg' => ['fields' => $validator->errors()] ], 400));
    }
}
