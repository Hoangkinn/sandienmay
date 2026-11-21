<?php

namespace App\Services\FeServices;

use App\DTOs\Product\ProductDetailDto;
use App\DTOs\Product\ProductListDto;
use App\Enums\CategoryType;
use App\Enums\Status;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\New_Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    private string $tableProduct;
    private string $tableImage;
    private string $tableCategoryPivot;
    public function __construct()
    {
        $productModel = resolve(New_Product::class);
        $this->tableImage = resolve(ProductImage::class)->getTable();
        $this->tableProduct = $productModel->getTable();
        $this->tableCategoryPivot = resolve(CategoryProduct::class)->getTable();

        $columnDefault = ["$this->tableProduct.id", "$this->tableProduct.name", 'slug', 'regular_price', 'regular_price_3', 'regular_price_5', 'sale_price', 'sold_quantity', 'warranty', 'brand_id', 'promotion', 'stock_status'];
        $this->selectList = array_merge($columnDefault, ['is_hot', 'is_new']);
        $this->selectDetail = array_merge($columnDefault, ['youtube_id', 'is_vat', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'seo_url_canonical']);

        parent::__construct($productModel);
    }
    public function getList(null|Builder|Relation $builder = null): Builder|Relation
    {
        return parent::getList()
            ->where('status', Status::ACTIVED)
            ->with([
                'images' => function (HasMany $query) {
                    $query->select(['product_id', 'url', 'has_primary'])
                        ->orderBy('has_primary', 'desc')
                        ->orderBy('id', 'asc');
                }
            ])
            ->orderBy('order', 'desc');
    }

    public function getByCateParents(array $ids, int $limit = 16): array
    {
        $parentIds = resolve(CategoryService::class)->getParentIds();
        $ids = array_intersect($ids, $parentIds);
        if (empty($ids)) return [];

        $keyCache = "getByCateParents$limit-" . implode('-', $ids);
        return Cache::remember($keyCache, 600,  function () use ($ids, $limit) {
            $query = $this->getList()->addSelect(DB::raw("{$ids[0]} as category_id"))->whereHas('categories', function ($q) use ($ids) {
                $q->where('category_id', $ids[0]);
            })->limit($limit);
            foreach (array_slice($ids, 1) as $id) {
                $query = $query->unionAll(
                    $this->getList()
                        ->addSelect(DB::raw("{$id} as category_id"))
                        ->whereHas('categories', function ($q) use ($id) {
                            $q->where('category_id', $id);
                        })->limit($limit)
                );
            }

            $products = $query->get();
            return ProductListDto::from($products);
        });
    }
    public function getNew(int $limit = 4): array
    {
        $keyCache = "newProducts$limit";
        return Cache::remember($keyCache, 600,  function () use ($limit) {
            $products = $this->getList()->where('is_new', 1)->limit($limit)->get();
            return ProductListDto::from($products);
        });
    }
    // public function search(string $s, int $limit = 10): array
    // {
    //     $products = $this->getList()
    //         ->whereRaw("LOWER(REPLACE($this->tableProduct.name, '-', ' ')) LIKE ?", ['%' . strtolower(str_replace('-', ' ', $s)) . '%'])
    //         ->limit($limit)
    //         ->get();

    //     return ProductListDto::from($products);
    // }
    public function getSearchQuery(string $s): Builder|Relation
    {
        $query = $this->getList();

        $keyword = trim($s);
        if ($keyword === '') {
            return $query;
        }

        $keywordLower = mb_strtolower($keyword);
        $specialChars = [' ', '-', '.', ',', '/'];
        $cleanKeyword = str_replace(array_merge($specialChars, ['\\']), '', $keywordLower);
        $words = array_filter(explode(' ', $keyword));

        // 1. Tìm các danh mục khớp với từ khóa (hoặc khớp tất cả các từ trong tên danh mục)
        $catQuery = Category::where('type', CategoryType::PRODUCT)
            ->where(function ($q) use ($keyword, $words) {
                $q->where('name', 'LIKE', "%{$keyword}%");
                if (count($words) > 1) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $w) {
                            if (mb_strlen($w) > 1) {
                                $subQ->where('name', 'LIKE', "%{$w}%");
                            }
                        }
                    });
                }
            });
        $matchedCatIds = $catQuery->pluck('id')->toArray();
        $allCatIds = $matchedCatIds;
        if (!empty($matchedCatIds)) {
            $childrenIds = Category::whereIn('parent_id', $matchedCatIds)->pluck('id')->toArray();
            $allCatIds = array_unique(array_merge($allCatIds, $childrenIds));
        }

        // 2. Tìm thương hiệu khớp với từ khóa
        $matchedBrandIds = Brand::where(function ($q) use ($keyword, $words) {
            $q->where('name', 'LIKE', "%{$keyword}%");
            foreach ($words as $w) {
                if (mb_strlen($w) > 2) {
                    $q->orWhere('name', 'LIKE', "%{$w}%");
                }
            }
        })->pluck('id')->toArray();

        // 3. Xây dựng điều kiện tìm kiếm sản phẩm
        $query->where(function ($q) use ($keyword, $cleanKeyword, $specialChars, $words, $allCatIds, $matchedBrandIds) {
            // A. Sản phẩm thuộc các danh mục tìm thấy
            if (!empty($allCatIds)) {
                $q->whereHas('categories', function ($catQ) use ($allCatIds) {
                    $catQ->whereIn('m_categories.id', $allCatIds);
                });
            }

            // B. Khớp chính xác cụm từ trong tên sản phẩm
            $q->orWhere($this->tableProduct . '.name', 'LIKE', '%' . $keyword . '%');

            // C. Khớp chuỗi sau khi loại bỏ dấu cách và ký tự đặc biệt
            $replaceSql = "LOWER(" . $this->tableProduct . ".name)";
            foreach ($specialChars as $char) {
                $replaceSql = "REPLACE($replaceSql, '$char', '')";
            }
            $replaceSql = "REPLACE($replaceSql, '\\\\', '')";
            if (mb_strlen($cleanKeyword) >= 3) {
                $q->orWhereRaw("$replaceSql LIKE ?", ["%{$cleanKeyword}%"]);
            }

            // D. Khớp tất cả các từ trong tên sản phẩm
            if (count($words) > 1) {
                $q->orWhere(function ($subQ) use ($words) {
                    foreach ($words as $word) {
                        if (mb_strlen($word) > 1) {
                            $subQ->where($this->tableProduct . '.name', 'LIKE', '%' . $word . '%');
                        }
                    }
                });

                // E. Kết hợp giữa tên sản phẩm và thương hiệu (VD: "máy hút bụi" thuộc brand "Kumisai")
                if (!empty($matchedBrandIds)) {
                    $nonBrandWords = [];
                    $brandNames = Brand::whereIn('id', $matchedBrandIds)->pluck('name')->toArray();
                    foreach ($words as $w) {
                        $isBrandWord = false;
                        foreach ($brandNames as $bName) {
                            if (stripos($bName, $w) !== false) {
                                $isBrandWord = true;
                                break;
                            }
                        }
                        if (!$isBrandWord && mb_strlen($w) > 1) {
                            $nonBrandWords[] = $w;
                        }
                    }
                    if (!empty($nonBrandWords)) {
                        $q->orWhere(function ($subQ) use ($matchedBrandIds, $nonBrandWords) {
                            $subQ->whereIn($this->tableProduct . '.brand_id', $matchedBrandIds);
                            foreach ($nonBrandWords as $nw) {
                                $subQ->where($this->tableProduct . '.name', 'LIKE', '%' . $nw . '%');
                            }
                        });
                    }
                }
            }
        });

        // 4. Sắp xếp độ ưu tiên kết quả tìm kiếm
        $query->reorder();

        $replaceOrderSql = "LOWER(" . $this->tableProduct . ".name)";
        foreach ($specialChars as $char) {
            $replaceOrderSql = "REPLACE($replaceOrderSql, '$char', '')";
        }
        $replaceOrderSql = "REPLACE($replaceOrderSql, '\\\\', '')";

        $catOrderSql = !empty($allCatIds) ? "CASE WHEN EXISTS (SELECT 1 FROM m_category_product WHERE m_category_product.product_id = " . $this->tableProduct . ".id AND m_category_product.category_id IN (" . implode(',', $allCatIds) . ")) THEN 1 ELSE 2 END ASC," : "";

        $query->orderByRaw("
            {$catOrderSql}
            CASE 
                -- Hạng 1: Tên bắt đầu đúng từ khóa
                WHEN LOWER(" . $this->tableProduct . ".name) LIKE CONCAT(?, '%') THEN 1
                
                -- Hạng 2: Khớp chính xác cụm từ nhưng đứng ở giữa hoặc cuối
                WHEN LOWER(" . $this->tableProduct . ".name) LIKE CONCAT('%', ?, '%') THEN 2

                -- Hạng 3: Khớp viết liền hoàn toàn (Ví dụ: kms80a)
                WHEN $replaceOrderSql LIKE CONCAT('%', ?, '%') THEN 3

                ELSE 4
            END ASC
        ", [
            $keywordLower, 
            $keywordLower, 
            $cleanKeyword  
        ]);

        $query->orderBy($this->tableProduct . '.order', 'desc');
        $query->orderBy($this->tableProduct . '.id', 'desc');

        return $query;
    }

    public function search(string $s, int $limit = 10): array
    {
        $products = $this->getSearchQuery($s)->limit($limit)->get();
        return ProductListDto::from($products);
    }
    public function increaseViewed(New_Product $product): void
    {
        $key = "pro_viewed_{$product->id}";
        $threshold = 10; 

        $views = Cache::increment($key);

        if ($views >= $threshold) {
            $product->increment('view', $views);

            Cache::forget($key);
        }
    }
    public function getSimilarProducts(int $productId, array $arrCatId, int $limit = 10): array
    {
        $keyCache = "getSimilarProducts{$productId}";
        return Cache::remember($keyCache, 600,  function () use ($productId, $arrCatId, $limit) {
            $products = $this->getList()
                ->whereIn('id', function ($query) use ($arrCatId, $productId) {
                    $query->select('product_id')
                        ->distinct()
                        ->from('m_category_product')
                        ->whereIn('category_id', $arrCatId)
                        ->where('product_id', '!=', $productId);
                })
                ->reorder('regular_price', 'desc')
                ->limit($limit)
                ->get();

            return ProductListDto::from($products);
        });
    }

    public function getProductsSale(): array
    {
        $keyCache = "saleProducts";
        return Cache::remember($keyCache, 600,  function () {
            // $products = $this->getList()->where('is_flash_sale', 1)->get();
            $products = $this->getList()->with('images')->where('is_flash_sale', 1)->get(); // Đảm bảo có with('images')
            return ProductListDto::from($products);
        });
    }

    public function getProductsHot(): array
    {
        $keyCache = "hotProducts";
        return Cache::remember($keyCache, 600, function () {
            $products = $this->getList()->where('is_hot', 1)->with('images')->get();

            return $products->map(function ($product) {
                $dto = new ProductListDto($product);
                $productArray = json_decode(json_encode($dto), true);

                $imagesCollection = $product->images ?? collect([]);
                $imagesArray = [];
                foreach ($imagesCollection as $img) {
                    if (!empty($img->url)) {
                        $imagesArray[] = $img->url;
                    }
                }
                
                if (empty($imagesArray) && !empty($product->image)) {
                    $imagesArray[] = $product->image;
                }

                $productArray['images'] = $imagesArray;

                return $productArray;
            })->toArray();
        });
    }

    public function getByCategory(int $id): array
    {
        $keyCache = "ProductsByCat_" . $id;

        return Cache::remember($keyCache, 600, function () use ($id) {
            $products = $this->getList()
                ->whereHas('categories', fn($q) => $q->where('m_categories.id', $id))
                ->limit(16)
                ->get();
            return ProductListDto::from($products);
        });
    }

    public function getAttributes(New_Product $product): array
    {
        return Cache::remember("attribute-product-$product->id", 10800, function () use ($product) {
            $attributeOptionService = resolve(AttributeOptionService::class);
            $items = $attributeOptionService->getList($product->attributeOptions())
                ->with([
                    'attribute:id,name'
                ])->get()->groupBy('attribute.name');

            return $items->map(function ($group) {
                return $group->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value,
                        // 'extra_price' => $item->pivot->extra_price ?? null,
                        // 'status' => $item->pivot->status ?? null,
                    ];
                })->toArray();
            })->toArray();
        });
    }

    public function getRecentProducts()
    {
        $recentProductIds = session()->get('recent_products', []);
        $products = $this->getList()
            ->whereIn('id', $recentProductIds)
            ->orderByRaw('FIELD(id,' . implode(',', $recentProductIds) . ')')
            ->get();
        return ProductListDto::from($products);
    }

    public function getLastProducts()
    {
        $products = $this->getList()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        return ProductListDto::from($products);
    }

    public function getByCategories(array $ids): Builder
    {
        return $items = $this->getList()
            ->whereHas('categories', fn($q) => $q->whereIn('m_categories.id', $ids));
    }
}
