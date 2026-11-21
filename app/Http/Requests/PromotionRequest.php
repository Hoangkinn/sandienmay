<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'from_date' => ['required'],
            'to_date' => ['required'],
            'content' => ['required', 'string'],
            'status' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Tiêu đề không được để trống',
            'from_date.required' => 'Từ ngày không được để trống',
            //'from_date.date' => 'Từ ngày không đúng định dạng',
            'to_date.required' => 'Đến ngày không được để trống',
            //'to_date.date' => 'Đến ngày không đúng định dạng',
            'content.required' => 'Nội dung không được để trống',
            'status.required' => 'Trạng thái không được để trống',
        ];
    }
}
