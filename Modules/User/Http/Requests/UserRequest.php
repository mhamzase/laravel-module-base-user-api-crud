<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $request = request();

        if(empty($request->user))
        {
            $rules = [
                'username' => ['required', 'string', 'max:255',         Rule::unique('users')],
                'email'    => ['required', 'string', 'email:rfc,dns',  'max:255',   Rule::unique('users')],
                'password' => ['required', 'string', 'min:8', 'confirmed']
            ];
        }
        else
        {
            $rules = [
                'username' => ['required', 'string', 'max:255',         Rule::unique('users')->ignore($request->user)],
                'email'    => [ 'string', 'email:rfc,dns', 'max:255',   Rule::unique('users')->ignore($request->user)],
                'password' => [ 'string', 'min:8', 'confirmed'],

            ];
        }

        $rules['status_id']   = 'required';
        $rules['lang']        = ['max:2'];
        $rules['roles']       = 'array';
        $rules['permissions'] = 'array';

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
