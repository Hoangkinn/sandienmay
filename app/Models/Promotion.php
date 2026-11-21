<?php

namespace App\Models;

class Promotion extends BaseModel
{
    protected $table = 'm_promotions';

    protected $fillable = [
        'title',
        'apply_for_child',
        'from_date',
        'to_date',
        'content',
        'user_id',
        'status',
    ];

}
