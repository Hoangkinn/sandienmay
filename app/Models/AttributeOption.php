<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class AttributeOption extends BaseModel
{
    protected $table = 'm_attribute_options';

    protected $fillable = [
        'name',
        'position',
        'status',
        'attribute_id',
        'user_id',
    ];

    public function isSelected($product_id, $attribute_id, $option_id)
    {
        $model =  ProductAttribute::where('product_id', $product_id)
        ->where('attribute_id', $attribute_id)
        ->where('option_id', $option_id)
        ->first();
        return $model != null ? 'selected' : '' ;
    }


    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'm_product_attributes');
    }

}
