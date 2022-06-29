<?php

namespace App\Extend\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\User\Http\Requests\UserRequest as BaseUserRequest;

class UserRequest extends ProxyUserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $request = request();

        $obj = new BaseUserRequest();
        $rules = $obj->rules();

        if (empty($this->request)) {
            $rules['phone'] = 'nullable|numeric|unique:user_fields';
        } else {
            $rules['phone'] = 'nullable|numeric|unique:user_fields,phone,' . $request->id;
        }

        $rules['age'] = 'required|numeric|min:18|max:100';
        $rules['gender'] = 'required|string';
        $rules['address'] = 'nullable|string';
        
        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
