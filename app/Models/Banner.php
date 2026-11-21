<?php

namespace App\Models;

class Banner extends BaseModel
{
    protected $table = 'm_banners';

    protected $fillable = [
        'title',
        'link',
        'image',
        'image_mobile',
        'position',
        'target',
        'order',
        'status',
        'user_id',
    ];
}
