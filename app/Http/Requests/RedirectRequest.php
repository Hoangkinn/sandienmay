<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
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
            'redirect_from' => ['required', 'string'],
            'redirect_to' => ['required', 'string'],
            'redirect_code' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'redirect_from.required' => 'Đường dẫn không được để trống',
            'redirect_to.required' => 'Đường dẫn không được để trống',
            'redirect_code.required' => 'Mã code không được để trống',
        ];
    }
}
