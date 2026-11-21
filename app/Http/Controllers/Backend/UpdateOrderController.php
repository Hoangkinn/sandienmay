<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;

class UpdateOrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrder($type, $id, $order)
    {
        if ($type == CategoryType::POST->value) {
            $model = Post::find($id);
        } else {
            $model = Product::find($id);
        }

        $model->order = $order;
        $model->save();

        return [
            'messages' => 'Cập nhật STT thành công'
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($type, $id, $status) {
        if ($type == CategoryType::POST->value) {
            $model = Post::find($id);
        } else {
            $model = Product::find($id);
        }

        $model->status = $status;
        if ($model->save()) {
            return [
                'messages' => 'Cập nhật trạng thái thành công'
            ];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStock($id, $status) {
        $product = Product::find($id);
        $product->stock_status = $status;
        if ($product->save()) {
            return [
                'messages' => 'Cập nhật hết hàng thành công'
            ];
        }
    }

}
