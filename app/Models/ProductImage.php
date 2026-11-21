<?php

namespace App\Models;

class ProductImage extends BaseModel
{
    protected $table = 'm_product_images';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'name',
        'url',
        'thumb_url',
        'has_primary',
    ];
}
