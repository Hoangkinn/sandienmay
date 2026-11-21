<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required', 'numeric', 'digits:10', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'customer_address' => ['required', 'string'],
            'order_comments' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'Họ tên không được để trống',
            'customer_email.required' => 'Địa chỉ email không được để trống',
            'customer_email.email' => 'Định dạng phải là email',
            'customer_phone.required' => 'Số điện thoại không được để trống',
            'customer_phone.numeric' => 'Định dạng phải là số',
            'customer_address.required' => 'Địa chỉ không được để trống',
            'customer_phone.digits' => 'Số điện thoại phải là 10 chữ số',
            'customer_phone.regex' => 'Số điện thoại không hợp lệ',
        ];
    }
}
