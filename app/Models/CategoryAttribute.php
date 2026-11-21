<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    protected $table = 'category_attribute';
    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'category_id',
    ];
}
