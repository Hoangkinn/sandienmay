<?php
namespace App\Services\FeServices;

use App\DTOs\Category\CategoryListDto;
use App\Enums\CategoryType;
use App\Enums\Status;
use App\Models\Category;
use App\Models\New_Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService extends BaseService
{
    public function __construct()
    {
        $cateModel = resolve(Category::class);
        $this->selectList = [$cateModel->getTable().'.id', 'type', 'name', 'slug', 'image', 'icon', 'order', 'parent_id', 'is_home_page', 'is_menu', 'is_featured', 'badge', 'display_type', 'seo_no_index', 'seo_title', 'seo_keywords', 'seo_description', 'seo_url_canonical', 'schema'];
        parent::__construct($cateModel);
    }
    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList($builder)->where('status', Status::ACTIVED)
            ->orderBy('order');
    }
    public function getProductCategories(): array
    {
        // $keyCache = "product_categories";
        // return Cache::rememberForever($keyCache,  function () {
        //     $items = $this->getList()->where('type', CategoryType::PRODUCT)->get();
        //     return CategoryListDto::from($items);
        // });
        $items = $this->getList()->where('type', CategoryType::PRODUCT)->get();
        return CategoryListDto::from($items);
    }

    /**
     * Mức giá + thông số cấu hình riêng cho từng danh mục cấp 1, dùng cho cột "Phân khúc giá"
     * và cột "Thông số" của menu/bộ lọc. Mảng key theo category_id:
     *   ['price' => [['label','min','max'], ...], 'spec' => [['attribute','option','option_id'], ...]]
     * TTL 10 phút thay vì rememberForever: admin sửa sản phẩm/thương hiệu không có hook nào
     * xoá cache này, để vĩnh viễn thì menu sẽ lệch dữ liệu mãi.
     */
    public function getFilterConfig(): array
    {
        return Cache::remember('fe_menu_filters_v1', 600, function () {
            $price = \DB::table('m_category_price_bands')
                ->orderBy('order')->orderBy('id')
                ->get(['category_id', 'label', 'min_price', 'max_price'])
                ->groupBy('category_id')
                ->map(fn ($rows) => $rows->map(fn ($r) => [
                    'label' => $r->label,
                    'min' => $r->min_price === null ? null : (int) $r->min_price,
                    'max' => $r->max_price === null ? null : (int) $r->max_price,
                ])->values()->all());

            $spec = \DB::table('m_category_specs')
                ->join('m_attribute_options', 'm_attribute_options.id', '=', 'm_category_specs.option_id')
                ->join('m_attributes', 'm_attributes.id', '=', 'm_category_specs.attribute_id')
                ->orderBy('m_category_specs.order')->orderBy('m_category_specs.id')
                ->get([
                    'm_category_specs.category_id',
                    'm_category_specs.option_id',
                    'm_attribute_options.name as option_name',
                    'm_attributes.name as attribute_name',
                ])
                ->groupBy('category_id')
                ->map(fn ($rows) => $rows->map(fn ($r) => [
                    'attribute' => $r->attribute_name,
                    'option' => $r->option_name,
                    'option_id' => (int) $r->option_id,
                ])->values()->all());

            $ids = $price->keys()->merge($spec->keys())->unique();

            return $ids->mapWithKeys(fn ($id) => [
                $id => [
                    'price' => $price->get($id, []),
                    'spec' => $spec->get($id, []),
                ],
            ])->all();
        });
    }
    public function getParentIds(): array
    {        $categories = $this->getProductCategories();

        $result = [];
        foreach ($categories as $item) {
            if (!$item['parent_id']) {
                $result[] = $item['id'];
            }
        }
        return $result;
    }
    public function getByProduct(New_Product $product): array
    {
        return Cache::rememberForever("cateByPro_v3-$product->id", function () use ($product) {
            $categories = resolve(CategoryService::class)->getList($product->categories())->get();
            $categories = CategoryListDto::from($categories);
            usort($categories, fn($a, $b) => $a['parent_id'] <=> $b['parent_id']);
            return $categories;
        });
    }
    public function getChildByCateId(int $id): array
    {
        $items = $this->getProductCategories();
        $result = [];
        foreach ($items as $i){
            if ($i['parent_id'] == $id)
                $result[] = $i;
        }
        return $result;
    }
    public function recursiveParent(array $category): array
    {
        $result = [];
        $categories = $this->getProductCategories();
        $category = Arr::first($categories, fn($i) => $i['id'] == $category['id']);

        while ($category) {
            $result[] = $category;
            $category = Arr::first($categories, fn($i) => $i['id'] == $category['parent_id']);
        }

        return array_reverse($result);
    }
    public function recursiveChildId(array $category): array
    {
        $categories = $this->getProductCategories();
        $categories = collect($categories)->groupBy('parent_id');
        return $this->getRecursiveIds($category['id'], $categories);
    }
    public function getParent(array $category): array
    {
        $items = $this->getProductCategories();
        $parentId = $category['parent_id'];
        return Arr::first($items, fn($i) => $i['id'] == $parentId) ?: $category;
    }
    private function getRecursiveIds(int $parentId, Collection $grouped, array &$results = []): array
    {
        $results[] = $parentId;

        if (!$grouped->has($parentId)) {
            return $results;
        }

        foreach ($grouped[$parentId] as $child) {
            $this->getRecursiveIds($child['id'], $grouped, $results);
        }

        return $results;
    }

    public function getProductCategoriesPageHome(): array
    {
        $keyCache = "product_categories_page_home";
        return Cache::rememberForever($keyCache,  function () {
            $items = $this->getList()->where('type', CategoryType::PRODUCT)->where('is_home_page', 1)->paginate(4);
            return CategoryListDto::from($items);
        });
    }

    public function getProductCategoriesParent(): array
    {
        // Đổi key cache từ v2 lên v3 để làm mới dữ liệu cũ
        $keyCache = "product_categories_parent_v3";
        
        return Cache::rememberForever($keyCache, function () {
            $items = $this->getList()
                ->where('type', CategoryType::PRODUCT)
                ->whereNull('parent_id')
                ->where('is_featured', 1) // <-- Thêm điều kiện lọc danh mục nổi bật ở đây
                ->get();
                
            return CategoryListDto::from($items);
        });
    }

    public function trees(): array
    {
        $keyCache = 'categories';
        return Cache::remember($keyCache, 600, function () {
            $allItems = $this->getProductCategories();

            $grouped = collect($allItems)
                ->map(fn($i) => array_merge($i, ['children' => []]))
                ->groupBy('parent_id');

            $build = function ($parentId) use (&$build, $grouped) {
                return ($grouped->get($parentId) ?? collect())
                    ->map(function ($item) use (&$build) {
                        $item['children'] = $build($item['id']);
                        return $item;
                    })
                    ->values()
                    ->all();
            };

            return $build(0);
        });
    }

    public function getCategoryByProduct(New_Product $product, bool $returnArray = true): array|Collection
    {
        $categories = $product->categories()->get($this->selectList);
        if ($categories->isEmpty()) return [];
        if ($returnArray)
            return CategoryListDto::from($categories);
        return $categories;
    }

    public function search(string $s, int $limit = 10): array
    {
        $products = $this->getList()->where('type', CategoryType::PRODUCT)->where("name", 'like', "%$s%")
            ->limit($limit)->get();
        return CategoryListDto::from($products);
    }
}
