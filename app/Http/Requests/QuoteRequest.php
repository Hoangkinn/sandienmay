<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => 'required|string|max:255',
            'phone'      => ['required', 'string', 'max:20', 'regex:/^[0-9+.\s()-]{8,20}$/'],
            'email'      => 'nullable|email|max:255',
            'product_id' => ['required', 'integer', Rule::exists('m_products', 'id')],
            'quantity'   => 'required|integer|min:1|max:999',
            'note'       => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'name.required'      => 'Vui lòng nhập họ tên.',
            'phone.required'     => 'Vui lòng nhập số điện thoại để sale liên hệ lại.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'phone.max'          => 'Số điện thoại không được vượt quá 20 ký tự.',
            'email.email'        => 'Email không hợp lệ.',
            'product_id.exists'  => 'Sản phẩm không tồn tại, vui lòng thử lại.',
            'quantity.min'       => 'Số lượng phải lớn hơn 0.',
            'note.max'           => 'Ghi chú không được vượt quá 2000 ký tự.',
        ];
    }
}
