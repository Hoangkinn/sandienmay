<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\FeServices\AttributeValueService;
use App\Services\FeServices\CartService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $quantity = $this->input('quantity');
        if (empty($quantity) || $quantity <= 0) {
            $this->merge(['quantity' => 1]);
        }
    }
    public function rules(): array
    {

        return [
            'id' => [$this->isMethod('post') ? 'required' : 'nullable', Rule::exists(Product::class, 'id')],
            // 'key' => [$this->isMethod('put') ? 'required' : 'nullable', function ($attribute, $value, $fail) {
            //     if (!resolve(CartService::class)->checkExists($value)){
            //         $fail('key', "Sản phẩm này không tồn tại");
            //     }
            // }],
            // 'atv' => ['nullable', 'array'],
            // 'atv.*' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
        ];
    }
    // public function withValidator($validator)
    // {
    //     if ($atvs = $this->get('atv')){
    //         $validator->after(function ($validator) use ($atvs) {
    //             $productId = $this->get('product_id');
    //             if (! resolve(AttributeValueService::class)->checkAttributeValueBeforeAddCart($productId, $atvs)){
    //                 $validator->errors()->add('atv', 'Dữ liệu đã thay đổi. Vui lòng tải lại trang');
    //             }
    //         });
    //     }
    // }

    public function messages(): array
    {
        return [
            '*' => 'Dữ liệu đã thay đổi. Vui lòng tải lại trang'
        ];
    }

    /*public function validated($key = null, $default = null): array
    {

    }*/
}
