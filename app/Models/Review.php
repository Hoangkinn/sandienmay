<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends BaseModel
{
    protected $fillable = [
        'name',
        'avatar',
        'content',
    ];

    public $timestamps = false;
}
