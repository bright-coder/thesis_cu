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
            '*.no' => 'required|string',
            '*.desc' => 'string',
            '*.inputs' => 'required|array|min:1',
            '*.inputs.*.name' => 'required|string',
            '*.inputs.*.dataType' => 'required|string|in:int,float,decimal,char,varchar,nchar,nvarchar,date,datetime',
            '*.inputs.*.length' => 'required_if:*.inputs.*.dataType,char,varchar,nchar,nvarchar|numeric',
            '*.inputs.*.precision' => 'required_if:*.inputs.*.dataType,float,decimal|numeric',
            '*.inputs.*.unique' => 'required|string|size:1|in:Y,N,y,n',
            '*.inputs.*.nullable' => 'required|string|size:1|in:Y,N,y,n',
            '*.inputs.*.tableName' => 'required|string|min:4',
            '*.inputs.*.columnName' => 'required|string|min:4',
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
