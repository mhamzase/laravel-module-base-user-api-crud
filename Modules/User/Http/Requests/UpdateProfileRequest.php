<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $request = request();
        $data = request()->all();
        $rules  = [
            //'username' => ['required', 'string', 'max:255',         Rule::unique('users')->ignore($request->user)],
            'email'    => [ 'string', 'email:rfc,dns', 'max:255',   Rule::unique('users')->ignore($request->user)],
            //'password' => [ 'string', 'min:8', 'confirmed'],
        ];

        if(!empty($data['password']))
        {
            $rules['password'] = 'min:8|confirmed';
        }

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
