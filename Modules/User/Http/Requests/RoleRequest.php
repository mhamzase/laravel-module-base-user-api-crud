<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $request = request();

        $rules   = [];

        if(empty($request->role))
        {
            $rules = [
                'name'       => 'required|max:255|' . Rule::unique('roles')
            ];
        }
        else
        {
            $rules = [
                'name'       => 'max:255|' . Rule::unique('roles')->ignore($request->role)
            ];
        }

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
