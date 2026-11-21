<?php

namespace App\Models;

class PromotionProduct extends BaseModel
{
    protected $table = 'm_promotion_product';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'promotion_id',
    ];

}
