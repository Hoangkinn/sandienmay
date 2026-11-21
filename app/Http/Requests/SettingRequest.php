<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'site_name' => ['required', 'string'],
            'site_tite' => ['required', 'string'],
            'site_keywords' => ['required', 'string'],
            'site_description' => ['required', 'string']
        ];
    }

    public function messages()
    {
        return [
            'site_name.required' => 'Tên website không được để trống',
            'site_tite.required' => 'Thẻ Meta Title không được để trống',
            'site_keywords.required' => 'Thẻ Meta Keywords không được để trống',
            'site_description.required' => 'Thẻ Meta Description không được để trống',
        ];
    }
}
