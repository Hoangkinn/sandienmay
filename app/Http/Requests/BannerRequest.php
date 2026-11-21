<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
            'image' => [
                'required',
                'image',
                // 'mimes:jpeg,png',
                // 'mimetypes:image/jpeg,image/png',
                'max:2048',
            ],
            'image_mobile' => [
                'nullable', // Không bắt buộc phải nhập
                'image',
                'max:2048',
            ],
            'order' => [
                'nullable',
                'integer',
            ],
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'Hình ảnh không được để trống',
            'image.image' => 'Banner phải là một hình ảnh.',

            'image_mobile.image' => 'Banner mobile phải là một hình ảnh.',

            'order.integer' => 'Số thứ tự phải là một số nguyên.',
        ];
    }
}