<?php

namespace App\Models;

class CategoryProduct extends BaseModel
{
    protected $table = 'm_category_product';

    protected $fillable = [
        'product_id',
        'category_id',
    ];

}
