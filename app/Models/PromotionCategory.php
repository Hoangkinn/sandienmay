<?php

namespace App\Models;

class PromotionCategory extends BaseModel
{
    protected $table = 'm_promotion_category';
    public $timestamps = false;
    protected $fillable = [
        'category_id',
        'promotion_id',
    ];

}
