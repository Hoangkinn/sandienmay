<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingFieldRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'field_name' => ['required', 'string'],
            'field_key' => ['required', 'string', 'unique:m_setting_fields'],
        ];
    }

    public function messages()
    {
        return [
            'field_name.required' => 'Field name không được để trống',
            'field_key.required' => 'Field key không được để trống',
            'field_key.unique' => 'Field key đã tồn tại',
        ];
    }
}
