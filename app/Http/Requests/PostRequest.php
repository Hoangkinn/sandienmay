<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'title' => ['required', 'string', 'unique:m_posts'],
            'slug' => ['required', 'string', 'unique:m_posts'],
            'excerpt' => ['nullable', 'string'], // Thêm 'nullable' để không bắt buộc nhập
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:100'], 
            'schema' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề không được để trống',
            'title.unique' => 'Tiêu đề đã tồn tại',
            'slug.required' => 'Đường dẫn không được để trống',
            'slug.unique' => 'Đường dẫn đã tồn tại',
            // 'excerpt.required' => 'Mô tả không được để trống',
            'content.required' => 'Nội dung không được để trống',
            'image.max' => 'Ảnh đại diện bài viết không được vượt quá 100KB. Vui lòng giảm dung lượng ảnh rồi tải lại.',
            'image.image' => 'Ảnh đại diện phải là một hình ảnh.',
            // 'image.required' => 'Hình ảnh không được để trống',
        ];
    }
}
