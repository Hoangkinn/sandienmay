<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048',
            ],
            'name' => [
                'required',
            ],
            'content' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'avatar.required' => 'Hình ảnh không được để trống',
            'avatar.image' => 'Ảnh đại diện phải là một hình ảnh.'
        ];
    }
}
