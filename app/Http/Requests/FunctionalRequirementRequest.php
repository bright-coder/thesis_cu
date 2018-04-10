<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class FunctionalRequirementRequest extends FormRequest
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
            'no' => 'required|string',
            'desc' => 'string',
            'inputs' => 'required|array|size:1',
            'inputs.*.no' => 'required|string',
            'inputs.*.name' => 'required|string',
            'inputs.*.dataType' => 'required|string',
            'inputs.*.unique' => 'required|string|size:1',
            'inputs.*.nullable' => 'required|string|size:1',
            'inputs.*.tableName' => 'required|string|size:1',
            'inputs.*.columnName' => 'required|string|size:1',
        ];
    }

    // public function message()
    // {
    //     return [

    //     ];
    // }

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