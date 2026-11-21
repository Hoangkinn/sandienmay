<?php

namespace App\Models;

class CategoryPost extends BaseModel
{
    protected $table = 'm_category_post';

    protected $fillable = [
        'post_id',
        'category_id',
    ];
}
