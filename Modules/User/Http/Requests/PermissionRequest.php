<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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

        if(empty($request->permission))
        {
            $rules = [
                'name'       => 'required|max:255|' . Rule::unique('permissions'),
                'guard_name' => 'max:255',
            ];
        }
        else
        {
            $rules = [
                'name'       => 'max:255|' . Rule::unique('permissions')->ignore($request->role),
                'guard_name' => 'max:255',
            ];
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
