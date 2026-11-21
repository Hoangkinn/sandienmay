<?php

namespace App\Models;

use App\Enums\Vat;

class OrderItem extends BaseModel
{
    protected $table = 'm_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'is_vat',
        'is_deleted'
    ];

    protected $casts = [
        'is_vat' => Vat::class,
    ];

    public function product()
    {
        return Product::find($this->product_id);
    }
}
