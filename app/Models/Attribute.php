<?php

namespace App\Models;

use App\Enums\Status;

class Attribute extends BaseModel
{
    protected $table = 'm_attributes';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'parent_id',
        'is_search',
        'is_display',
        'position',
        'status',
        'user_id'
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    public function options()
    {
        return $this->hasMany(AttributeOption::class);
    }
                                                                                            
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute');
    }
}
