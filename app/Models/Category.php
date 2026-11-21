<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Enums\Status;

class Category extends BaseModel
{
    protected $table = 'm_categories';

    protected $fillable = [
        'name',
        'slug',
        'excerpt',
        'image',
        'order',
        'status',
        'parent_id',
        'type',
        'is_home_page',
        'seo_no_index',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'seo_url_canonical',
        'user_id',
        'status_similar_product',
        'is_menu',
        'is_featured',
        'badge',
        'coupon_data',
        'display_type',
        'schema',
    ];

    protected $casts = [
        'status' => Status::class,
        'coupon_data' => 'array',
    ];

    public function childs($id = null)
    {
        $query = $this->hasMany(Category::class, 'parent_id', 'id');
        if ($id) {
            $query = $query->where('id', '!=', $id);
        }
        return $query;
    }

    public function childs2($id = null)
    {
        $query = $this->hasMany(Category::class, 'parent_id', 'id')
            ->where('status', Status::ACTIVED->value);
        if ($id) {
            $query = $query->where('id', '!=', $id);
        }
        return $query;
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_category');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'm_category_product')->orderBy('order','desc');
    }

    public function products2()
    {
        return $this->belongsToMany(Product::class, 'm_category_product')->where('m_products.status', 'actived');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'm_category_post')->orderByDesc('order')->orderByDesc('created_at')->limit(10);
    }

    public function posts2()
    {
        return $this->belongsToMany(Post::class, 'm_category_post')->where('m_posts.status', 'actived');
    }

    public function countProduct()
    {
        return $this->belongsToMany(Product::class, 'm_category_product')->count();
    }

    public function countPost()
    {
        return $this->belongsToMany(Post::class, 'm_category_post')->count();
    }

    public function isSelected($category_id, $promotion_id)
    {
        $model =  PromotionCategory::where('category_id', $category_id)
            ->where('promotion_id', $promotion_id)
            ->first();
        return $model != null ? 'selected' : '';
    }

    public function isSelectedByType($productOrPostId, $type = 'post')
    {
        if ($type == CategoryType::PRODUCT->value) {
            $model = CategoryProduct::where('category_id', $this->id)
                ->where('product_id', $productOrPostId)
                ->first();
        } else {
            $model = CategoryPost::where('category_id', $this->id)
                ->where('post_id', $productOrPostId)
                ->first();
        }

        return $model != null ? 'checked' : '';
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

    public function inCategoryIds($categoryIds = []) {
        if ($categoryIds && in_array($this->id, $categoryIds)) {
            return 'checked';
        }
    }

    public function selectMultiple($categoryIds = []) {
        if ($categoryIds && in_array($this->id, $categoryIds)) {
            return 'selected';
        }
    }

    public function getBreadcrumb() {
        $category = Category::find($this->id);
        $breadcrumb = '';
        if ($category->parent_id) {
            $breadcrumb .= $category->getBreadcrumbParent($category->parent_id);
        }
        $breadcrumb .= '<li><a href="'. route('frontend.slug', ['slug' => $category->slug]) . '"><span>'.$category->name.'</span></a></li>';

        return $breadcrumb;
    }

    public function getBreadcrumbParent($parent_id) {
        $category = Category::find($parent_id);

        $breadcrumb = '';
        if ($category->parent_id) {
            $breadcrumb .= $category->getBreadcrumbParent($category->parent_id);
        }
        $breadcrumb .= '<li><a href="'. route('frontend.slug', ['slug' => $category->slug]) . '">' . $category->name . '</a></li>';

        return $breadcrumb;
    }

    public function brands(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        // Quan hệ với bảng brands thông qua bảng pivot category_brand_pivot
        return $this->belongsToMany(Brand::class, 'category_brand_pivot', 'category_id', 'brand_id');
    }

    /** Mức giá cấu hình riêng cho danh mục cấp 1 (thay cho danh mục con display_type=price) */
    public function priceBands(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CategoryPriceBand::class, 'category_id')->orderBy('order')->orderBy('id');
    }

    /** Thông số (giá trị thuộc tính) gắn với danh mục cấp 1 */
    public function specs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CategorySpec::class, 'category_id')->orderBy('order')->orderBy('id');
    }
}
