<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return
        [
            'title'      => 'required',
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|max:255|unique:users',
            'gender_id'  => '',
            'phone1'     => 'nullable|numeric',
            'phone2'     => 'nullable|numeric',

            'addr1'      => '',
            'addr2'      => '',
            'country'    => 'required',
            'state'      => 'required',
            'city'       => 'required',
            'zip_code'   => 'required',

            'username'   => 'required|unique:users',
            'password'   => 'required|confirmed',
            'password_confirmation'   => 'required',

            'store.title'        => 'required|max:255',
            'store.description'  => '',
            'store.tagline'      => 'nullable|max:255',
            'store.category_id'  => 'nullable|integer',
            'store.extra'        => 'nullable',

            'type_id'            => 'nullable',

        ];
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
