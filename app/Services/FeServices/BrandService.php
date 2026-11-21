<?php
namespace App\Services\FeServices;

use App\DTOs\BrandListDto;
use App\Models\Brand;
use App\Models\Category;
use App\Models\New_Product;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class BrandService extends BaseService
{
    public function __construct()
    {
        $this->selectList = ['id', 'name', 'image','order', 'is_active'];
        parent::__construct(resolve(Brand::class));
    }
    public function getList(null|Relation|Builder $builder = null): Relation|Builder
    {
        return parent::getList($builder)->where('is_active', 1)->orderByDesc('order');
    }

    public function getByCategory(Category $category): array
    {
        return Cache::remember("brand-cate-$category->id", 10800, function () use ($category) {
            $items = $this->getList()
                ->whereHas('products.categories', fn($q) => $q->where('m_categories.id', $category->id))
                ->get();
            return BrandListDto::from($items);
        });
    }
    public function getByProduct(New_Product $product): array
    {
        return Cache::remember("brand-product-$product->id", 10800, function () use ($product) {
            return BrandListDto::from($product->brand()->first($this->selectList));
        });
    }
}
