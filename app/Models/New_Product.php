<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Status;
use App\Enums\Vat;

class New_Product extends BaseModel
{
    protected $table = 'm_products';

    protected $guarded = [
        'id'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'm_category_product', 'product_id', 'category_id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')
                    ->orderBy('has_primary', 'desc')
                    ->orderBy('id', 'asc');
    }

    public function scopeBetweenPrice(
        Builder $query,
        float|int|null $min = null,
        float|int|null $max = null
    ): Builder {
        // Lọc sản phẩm có ít nhất 1 giá > 0
        $query->where(function ($q) {
            $q->where('sale_price', '>', 0)
                ->orWhere('regular_price', '>', 0);
        });

        // Sử dụng giá trị ảo lớn để loại bỏ giá = 0 khỏi LEAST
        $leastExpr = "
        LEAST(
            IF(sale_price > 0, sale_price, 999999999),
            IF(regular_price > 0, regular_price, 999999999)
        )
    ";

        // if (!is_null($min) && !is_null($max) && $max > 0) {
        //     $query->whereRaw("$leastExpr BETWEEN ? AND ?", [$min, $max]);
        // } elseif (!is_null($min)) {
        //     $query->whereRaw("$leastExpr > ?", [$min]);
        // } elseif (!is_null($max) && $max > 0) {
        //     $query->whereRaw("$leastExpr <= ?", [$max]);
        // }

        if (!is_null($min) && !is_null($max)) {
            $query->whereRaw("$leastExpr BETWEEN ? AND ?", [$min, $max]);
        } elseif (!is_null($min)) {
            $query->whereRaw("$leastExpr >= ?", [$min]);
        } elseif (!is_null($max)) {
            $query->whereRaw("$leastExpr <= ?", [$max]);
        }

        return $query;
    }

    public function scopeSortPrice(Builder $query, bool $asc = true): Builder
    {
        $priceExpr = "
            LEAST(
                IF(sale_price > 0, sale_price, 999999999),
                IF(regular_price > 0, regular_price, 999999999)
            )
        ";

        return $query
            ->orderByRaw("
                CASE 
                    WHEN (sale_price <= 0 AND regular_price <= 0) THEN 1
                    ELSE 0
                END
            ")
        ->orderByRaw("$priceExpr " . ($asc ? 'ASC' : 'DESC'));
    }


    public function comments()
    {
        return $this->hasMany(Comment::class, 'product_id', 'id')->where('status', 'yes');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'm_product_attributes');
    }

    public function getProductAttributes()
    {
        $queryAttributes = Attribute::select('m_attributes.id', 'm_attributes.name')
            ->distinct()
            ->join('m_product_attributes', 'm_product_attributes.attribute_id', '=', 'm_attributes.id')
            ->where('m_product_attributes.product_id', $this->id)
            ->where('m_attributes.status', Status::ACTIVED->value)
            ->orderByRaw('CASE WHEN m_product_attributes.position IS NULL THEN 1 ELSE 0 END, m_product_attributes.position ASC')
            ->get();

        $attributes = $queryAttributes->toArray();
        foreach ($attributes as $key => $attribute) {
            $attributes[$key]['options'] = [];

            $queryOptions = AttributeOption::select('m_attribute_options.name')
                ->join('m_product_attributes', 'm_product_attributes.option_id', '=', 'm_attribute_options.id')
                ->where('m_product_attributes.product_id', $this->id)
                ->where('m_product_attributes.attribute_id', $attribute['id'])
                ->where('m_attribute_options.status', Status::ACTIVED->value)
                ->orderByRaw('CASE WHEN m_product_attributes.position IS NULL OR m_product_attributes.position = 0 THEN 1 ELSE 0 END, m_product_attributes.position ASC')
                ->get();

            $attributes[$key]['options'] = $queryOptions->toArray();
        }

        return $attributes;
    }

    public function getArrProductAttributes()
    {
        $queryAttributes = Attribute::select('m_attributes.id', 'm_attributes.name')
            ->distinct()
            ->join('m_product_attributes', 'm_product_attributes.attribute_id', '=', 'm_attributes.id')
            ->where('m_product_attributes.product_id', $this->id)
            ->where('m_attributes.status', Status::ACTIVED->value)
            ->orderBy('m_product_attributes.position')
            ->get();

        $result = [];

        foreach ($queryAttributes as $attribute) {

            $options = AttributeOption::select('m_attribute_options.name')
                ->join('m_product_attributes', 'm_product_attributes.option_id', '=', 'm_attribute_options.id')
                ->where('m_product_attributes.product_id', $this->id)
                ->where('m_product_attributes.attribute_id', $attribute->id)
                ->where('m_attribute_options.status', Status::ACTIVED->value)
                ->orderBy('m_product_attributes.position')
                ->get()
                ->pluck('name')
                ->toArray();

            $result[$attribute->name] = array_map(fn($o) => ['value' => $o], $options);
        }

        return $result;
    }
}
