<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectRequest extends FormRequest
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
                'projectName' => 'required|between:10,255',
                'dbName' => 'required|between:1,255',
                'dbServer' => 'required|between:1,255',
                'dbPort' => 'required|numeric',
                'dbType' => 'required',
                'dbUsername' => 'required|between:4,100',
                'dbPassword' => 'required|between:4,100'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'This field is required.',
            'between' => 'This field must contain :min - :max characters.',
            'numeric' => 'This field must contain number only.'
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
