<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends BaseModel
{
    use IsActiveTrait;
    protected $table = 'brands';

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_brand_pivot');
    }
}
