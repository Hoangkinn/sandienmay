<?php

namespace App\Models;

class Faq extends BaseModel
{
    protected $table = 'm_faqs';

    protected $fillable = [
        'question',
        'answer',
        'user_id',
    ];
}
