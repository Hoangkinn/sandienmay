<?php

namespace App\Models;

class EmailSubscriber extends BaseModel
{
    protected $table = 'email_subscribers';

    protected $fillable = [
        'email',
    ];
}
