<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\ProductStatus;
use App\Enums\Status;
use App\Enums\Vat;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends BaseModel
{
    protected $table = 'm_products';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'brand_id',
        'description',
        'content',
        'promotion',
        'sku',
        'model',
        'quantity',
        'regular_price',
        'regular_price_3',
        'regular_price_5',
        'sale_price',
        'flash_sale_price',
        'stock_status',
        'status',
        'is_vat',
        'is_new',
        'is_hot',
        'is_flash_sale',
        'timeout',
        'order',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'seo_url_canonical',
        'seo_no_index',
        'user_id',
        'youtube_id',
        'view',
        'warranty'
    ];

    protected $casts = [
        'status' => Status::class,
        'stock_status' => ProductStatus::class,
        'is_vat' => Vat::class,
    ];

    public function getPrice()
    {
        return $this->regular_price ? number_format($this->regular_price) : 0;
    }

    public function getPrice3()
    {
        return $this->regular_price_3 ? number_format($this->regular_price_3) : 0;
    }

    public function getPrice5()
    {
        return $this->regular_price_5 ? number_format($this->regular_price_5) : 0;
    }

    public function getSalePrice()
    {
        return $this->sale_price ? number_format($this->sale_price) : 0;
    }

    public function getPercent()
    {
        if ($this->sale_price && $this->regular_price) {
            return  round((($this->sale_price - $this->regular_price) / $this->regular_price) * 100) . '%';
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'm_category_product')->orderByPivot('m_category_product.created_at', 'desc');
    }

    public function getStockStatusName()
    {
        switch ($this->stock_status->value) {
            case ProductStatus::IN_STOCK->value:
                $className = 'success';
                break;
            default:
                $className = 'danger';
                break;
        }

        return '<span class="badge bg-' . $className . '">' . $this->stock_status->label() . '</span>';
    }

    public function getStatusName()
    {
        switch ($this->status->value) {
            case Status::ACTIVED->value:
                $className = 'success';
                break;
            default:
                $className = 'danger';
                break;
        }

        return '<span class="badge bg-' . $className . '">' . $this->status->label() . '</span>';
    }

    public function isSelected($product_id, $promotion_id)
    {
        $model = PromotionProduct::where('product_id', $product_id)
            ->where('promotion_id', $promotion_id)
            ->first();
        return $model != null ? 'selected' : '';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'product_id', 'id')->where('status', CommentStatus::YES->value);
    }

    public function countRank()
    {
        return $this->hasMany(Comment::class, 'product_id', 'id')->where('status', CommentStatus::YES->value)->where('parent_id', null);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->select('name', 'url', 'thumb_url', 'has_primary as primary');
    }

    public function imagePrimary()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->where('has_primary', 1);
    }

    public function imageFirst()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->first();
    }

    public function createdUser()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getProductAttributes()
    {
        $queryAttributes = Attribute::select('m_attributes.id', 'm_attributes.name')
            ->distinct()
            ->join('m_product_attributes', 'm_product_attributes.attribute_id', '=', 'm_attributes.id')
            ->where('m_product_attributes.product_id', $this->id)
            ->where('m_attributes.status', Status::ACTIVED->value)
            ->orderBy('m_attributes.position')
            ->get();

        $attributes = $queryAttributes->toArray();
        foreach ($attributes as $key => $attribute) {
            $attributes[$key]['options'] = [];

            $queryOptions = AttributeOption::select('m_attribute_options.name')
            ->join('m_product_attributes', 'm_product_attributes.option_id', '=', 'm_attribute_options.id')
            ->where('m_product_attributes.product_id', $this->id)
            ->where('m_product_attributes.attribute_id', $attribute['id'])
            ->where('m_attribute_options.status', Status::ACTIVED->value)
            ->orderBy('m_attribute_options.position')
            ->get();

            $attributes[$key]['options'] = $queryOptions->toArray();
        }

        return $attributes;
    }

    public function getCountOrder() {
        $query = OrderItem::select('m_order_items.id')
            ->join('m_orders', 'm_order_items.order_id', '=', 'm_orders.id')
            ->where('m_order_items.product_id', $this->id)
            // ->where('m_orders.status', OrderStatus::COMPLETED->value)
            ->get();

        return $query->count();
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'm_product_attributes');
    }
}
