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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'functionRequirementId' => 'required|string',
            'inputs' => 'required|array|min:1',
            'inputs.*.changeType' => 'required|string|in:add,edit,delete',
            'inputs.*.name' => 'required_if:inputs.*.changeType,add|string',
            'inputs.*.dataType' => 'required_if:inputs.*.changeType,add,edit|string|in:int,float,decimal,char,varchar,nchar,nvarchar,date,datetime',
            'inputs.*.length' => 'required_if:inputs.*.dataType,char,varchar,nchar,nvarchar|numeric',
            'inputs.*.precision' => 'required_if:inputs.*.dataType,float,decimal|numeric',
            'inputs.*.unique' => 'required_if:inputs.*.changeType,add,edit|string|size:1|in:Y,N,y,n',
            'inputs.*.nullable' => 'required_if:inputs.*.changeType,add,edit|string|size:1|in:Y,N,y,n',
            'inputs.*.tableName' => 'required_if:inputs.*.changeType,add,edit|string|min:4',
            'inputs.*.columnName' => 'required_if:inputs.*.changeType,add,edit|string|min:4',

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
