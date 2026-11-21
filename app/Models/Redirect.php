<?php

namespace App\Models;

class Redirect extends BaseModel
{
    protected $table = 'm_redirects';

    protected $fillable = [
        'redirect_from',
        'redirect_to',
        'redirect_code',
        'user_id',
    ];

}
