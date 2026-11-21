<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'slug' => ['required', 'string'],
            'schema' => ['nullable', 'string'],
            'badge' => ['nullable', 'in:hot,new'],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục không được để trống',
            'slug.required' => 'Đường dẫn không được để trống',
            'badge.in' => 'Nhãn HOT/NEW không hợp lệ',
            'icon.max' => 'Ảnh Thumb không được vượt quá 100KB. Vui lòng giảm dung lượng ảnh rồi tải lại.',
            'icon.image' => 'Ảnh Thumb phải là một hình ảnh.',
        ];
    }
}
