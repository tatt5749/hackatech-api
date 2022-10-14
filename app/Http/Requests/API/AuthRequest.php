<?php

namespace App\Http\Requests\API;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class AuthRequest extends BaseRequest
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
    
    public function loginEmail()
    {
        $rule = [
            'email' => ['required','string'],
		    'password' => ['required','string'],
		    'token' => ['nullable','string'],
        ];
        return $rule;
    }
    
    public function loginPhone()
    {
        $rule = [
            'phone' => ['required','string'],
		    'password' => ['required','string'],
		    'token' => ['nullable','string'],
        ];
        return $rule;
    }
    
  


    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throwApiErrorException($errors);
        //throw new HttpResponseException(httpResponse()::httpFail($errors));
    }
}
