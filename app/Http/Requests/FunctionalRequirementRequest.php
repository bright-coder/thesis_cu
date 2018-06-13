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
            '*.no' => 'required|numeric',
            '*.desc' => 'string',
            '*.inputs' => 'required|array|min:1',
            '*.inputs.*.name' => 'required|string|min:1|max:50',
            '*.inputs.*.tableName' => 'required|string|min:1|max:50',
            '*.inputs.*.columnName' => 'required|string|min:1|max:50',
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
