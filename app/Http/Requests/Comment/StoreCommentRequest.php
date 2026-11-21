<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'author' => ['required', 'string'],
            'author_email' => ['required', 'string', 'regex:/^[0-9]{10,11}$/'],
            'content' => ['required', 'string'],
            'rating' => 'nullable|integer|min:1|max:5',
        ];
    }

    public function messages()
    {
        return [ 
            'author.required' => 'Họ tên không được để trống',
            'author_email.required' => 'Số điện thoại không được để trống',
            'author_email.regex' => 'Số điện thoại không đúng định dạng',
            'content.required' => 'Nội dung đánh giá không được để trống',
        ];
    }
}
