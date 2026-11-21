<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Requests\ContactRequest;
use App\Http\Requests\QuoteRequest;
use App\Models\Contact;
use App\Models\Product;


class ContactController extends BaseController
{
    public function store(ContactRequest $request)
    {
        Contact::create($request->validated());

        return response()->json([
            'status'  => 200,
            'message' => 'Gửi liên hệ thành công!',
        ]);
    }

    /**
     * Nút "Yêu cầu báo giá" trên trang sản phẩm (PC). Lưu chung bảng contacts để sale có sẵn
     * danh sách ở /backend/contact, nên tên sản phẩm + số lượng phải nhét vào content.
     * Tên sản phẩm lấy từ DB theo product_id, không lấy chuỗi client gửi lên.
     */
    public function quote(QuoteRequest $request)
    {
        $data = $request->validated();
        $product = Product::find($data['product_id']);

        $lines = [
            '[Yêu cầu báo giá] ' . ($product ? $product->name : 'Sản phẩm #' . $data['product_id'])
                . ' — Số lượng: ' . $data['quantity'],
        ];
        if ($product) {
            $lines[] = 'Trang sản phẩm: ' . route('frontend.slug', ['slug' => $product->slug]);
        }
        if (!empty($data['note'])) {
            $lines[] = 'Ghi chú: ' . trim($data['note']);
        }

        Contact::create([
            'name'    => $data['name'],
            'email'   => $data['email'] ?? null,
            'phone'   => $data['phone'],
            'content' => implode("\n", $lines),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Đã gửi yêu cầu báo giá! Bộ phận kinh doanh sẽ liên hệ lại với bạn.',
        ]);
    }
}
