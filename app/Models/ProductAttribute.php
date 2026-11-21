<?php

namespace App\Models;

class ProductAttribute extends BaseModel
{
    protected $table = 'm_product_attributes';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'attribute_id',
        'option_id',
        'position'
    ];
}
