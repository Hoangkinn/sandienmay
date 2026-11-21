<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Category\CategoryDetailDto;
use App\DTOs\Category\CategoryListDto;
use App\DTOs\Product\ProductDetailDto;
use App\DTOs\Product\ProductListDto;
use App\Enums\ProductCategoryFilterPrice;
use App\Enums\ProductCategorySort;
use App\Helpers\AgentHelper;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use App\Models\Category;
use App\Models\New_Product;
use App\Services\FeServices\BrandService;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\CommentService;
use App\Services\FeServices\ProductService;
use App\Services\FeServices\SettingFieldService;
use App\Services\RequestService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Enums\Status;


class New_ProductController extends BaseController
{
    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected CommentService $commentService,
        protected BrandService $brandService
    ) {
        parent::__construct();
    }

    public function product(New_Product $product): View|JsonResponse
    {
        
        $checkStatus = New_Product::where('id', $product->id)
            ->where('status', Status::ACTIVED)
            ->exists();

        // Nếu không phải actived thì cho bay màu về 404 luôn
        if (!$checkStatus) {
            abort(404);
        }
        
        $realProductId = $product->id;

        $this->recentProducts($product);
        $this->productService->increaseViewed($product);
        $reviewService = resolve(CommentService::class);
        $statsReview = $reviewService->getReviewStatsByProduct($product);
        $reviewAvg = $statsReview['avg'];
        $reviewTotal = $statsReview['total'];
        $brand = resolve(BrandService::class)->getByProduct($product);

        if ($this->request->ajax()) {
            if ($this->request->get('type') == 'getFirstPage') {
                $html = $this->view('product/_first-screen', [
                    'product' => ProductDetailDto::from($product),
                    'reviewAvg' => $reviewAvg,
                    'reviewTotal' => $reviewTotal,
                    'brand' => $brand,
                    'categoryCoupons' => $this->collectCategoryCoupons($this->categoryService->getByProduct($product)),
                    'showFull' => false
                ])->render();

                $html = str_replace(array("\r", "\n", "\t"), '', $html);

                return response()->json([
                    'html' => $html
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }

        $productCategories = $this->categoryService->getByProduct($product);
        foreach ($productCategories as $i) {
            BreadcrumbHelper::add($i['name'], $i['url']);
        }

        // $categoryCoupons = [];
        // if (!empty($productCategories) && isset($productCategories[0]['id'])) {
        //     $categoryModel = \App\Models\Category::find($productCategories[0]['id']);
        //     if ($categoryModel) {
        //         $categoryCoupons = $categoryModel->coupon_data ?? [];
        //     }
        // }
        $categoryCoupons = $this->collectCategoryCoupons($productCategories);

        

        $phoneHn = SettingFieldService::getKey('new_phone_hn');
        $phoneHcm = SettingFieldService::getKey('new_phone_hcm');
        $addressHn = SettingFieldService::getKey('address_hn');
        $addressHcm = SettingFieldService::getKey('address_hcm');
        $productAttributes = $product->getProductAttributes();
        $reviews = $reviewService->getByProduct($product, 10);
        $questions = $reviewService->getQuestionThreadsByProduct($product);
        $voteStats = $reviewService->getVoteDistributionByProduct($product);
        
        
        // Nạp mảng dữ liệu ảnh từ Model sang trước khi chuyển đổi DTO
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

        $productDto = ProductDetailDto::from($product);
        
        $productArray = is_object($productDto) ? json_decode(json_encode($productDto), true) : $productDto;
        
        $productArray['id'] = $realProductId; 
        $productArray['images'] = !empty($productArray['images']) ? $productArray['images'] : $imagesArray;

        $product = $productArray;

        // SEO của trang sản phẩm: 27/27 sản phẩm có seo_title/seo_description NULL → DTO cast (string)
        // nên mảng ra '', mà `??` không coi '' là rỗng → <title> trống không. Dùng `?:` như dòng
        // seoUrlCanonical bên dưới và như CategoryController:127.
        SeoHelper::setTitle($product['seoTitle'] ?: ($product['name'] ?: ''));
        SeoHelper::setDescription($product['seoDescription'] ?: ($product['name'] ?: ''));
        SeoHelper::setKeywords($product['seoKeywords'] ?: ($product['name'] ?: ''));
        SeoHelper::setUrl($product['seoUrlCanonical'] ?: ($product['url'] ?? url()->current()));
        
        if (!empty($product['images']) && isset($product['images'][0])) {
            SeoHelper::setImage($product['images'][0]);
        }

        $isDesktop = ! AgentHelper::isMobile();
        $relateProducts = $this->productService->getSimilarProducts($product['id'], array_column($productCategories, 'id'));

        // Điểm đánh giá trung bình cho khối "Sản phẩm tương tự" (1 query gộp, không load theo từng card)
        $relateStats = \DB::table('m_comments')
            ->whereIn('product_id', array_column($relateProducts, 'id'))
            ->where('type', 'product')
            ->where('status', 'yes')
            ->whereNotNull('rating')
            ->selectRaw('product_id, ROUND(AVG(rating), 1) as avg_rating, COUNT(id) as total_rating')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        foreach ($relateProducts as $ri => $rp) {
            $relateProducts[$ri]['review_avg'] = (float) ($relateStats[$rp['id']]->avg_rating ?? 0);
            $relateProducts[$ri]['review_total'] = (int) ($relateStats[$rp['id']]->total_rating ?? 0);
        }

        $recentProducts = $this->productService->getRecentProducts();

        $parentCategory = null;
        if (!empty($productCategories)) {
            $parentCategory = $this->categoryService->getParent($productCategories[0]);
        }
        $searchTargetName = $parentCategory ? $parentCategory['name'] : $productCategories[0]['name'] ?? '';
        $keyword = preg_split('/( công nghiệp| cầm tay| mini| gia đình| giá rẻ)/i', $searchTargetName)[0];

        $newsCategory = \DB::table('m_categories')
            ->where('type', 'post')
            ->where('name', 'like', '%' . $keyword . '%')
            ->first();
        $news = collect();
        if ($newsCategory) {
            $newsPosts = \App\Models\Post::where('status', \App\Enums\Status::ACTIVED->value)
                ->hasThumb()
                ->whereHas('categories', function ($query) use ($newsCategory) {
                    $query->where('m_categories.id', $newsCategory->id);
                })
                ->latest('created_at')
                ->limit(5)
                ->get();

            $news = collect(\App\DTOs\Post\PostListDto::from($newsPosts))->map(function ($item) {
                $item['name'] = $item['title']; // Đổi title thành name để thống nhất hiển thị
                return $item;
            });
        }

        $recentIds = session()->get('recent_products', []);
        $viewedProducts = collect();
        $viewedPaginate = null;

        if (!empty($recentIds)) {
            $products = \App\Models\New_Product::whereIn('id', $recentIds)
                ->with(['images'])
                ->where('status', \App\Enums\Status::ACTIVED->value)
                ->get();

            $sortedProducts = collect($recentIds)->map(function ($id) use ($products) {
                return $products->firstWhere('id', $id);
            })->filter();

            $dtoItems = collect(\App\DTOs\Product\ProductListDto::from($sortedProducts));

            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage('viewed_page');
            $currentItems = $dtoItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $viewedProducts = new LengthAwarePaginator(
                $currentItems,
                $dtoItems->count(),
                $perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'viewed_page'
                ]
            );

            $viewedPaginate = $viewedProducts->links();
        }

        return $this->view('product/detail', compact(
            'product',
            'productCategories',
            'phoneHn',
            'phoneHcm',
            'addressHn',
            'addressHcm',
            'relateProducts',
            'reviewAvg',
            'reviewTotal',
            'productAttributes',
            'reviews',
            'questions',
            'voteStats',
            'recentProducts',
            'brand',
            'isDesktop',
            'news',
            'viewedPaginate',
            'viewedProducts',
            'categoryCoupons'
        ));
    }

    /**
     * Mã khuyến mãi của sản phẩm: chỉ quét từ danh mục trực tiếp và danh mục cha của nó
     */
    private function collectCategoryCoupons($productCategories): array
    {
        $categoryCoupons = [];
        $validCategoryIds = [];

        if (!empty($productCategories)) {
            foreach ($productCategories as $cat) {
                $catId = is_array($cat) ? ($cat['id'] ?? null) : ($cat->id ?? null);
                if ($catId) {
                    $validCategoryIds[] = $catId;

                    // Lấy thêm đúng ID danh mục cha trực tiếp của danh mục này (nếu có)
                    $categoryModel = \App\Models\Category::find($catId);
                    if ($categoryModel && $categoryModel->parent_id) {
                        $validCategoryIds[] = $categoryModel->parent_id;
                    }
                }
            }
        }
        $validCategoryIds = array_unique($validCategoryIds);

        // Không quét lan sang danh mục khác
        $categoriesWithCoupons = \App\Models\Category::whereIn('id', $validCategoryIds)
            ->whereNotNull('coupon_data')
            ->get();

        foreach ($categoriesWithCoupons as $cat) {
            $coupons = is_string($cat->coupon_data) ? json_decode($cat->coupon_data, true) : $cat->coupon_data;
            if (is_array($coupons)) {
                foreach ($coupons as $coupon) {
                    $codeKey = $coupon['code'] ?? '';
                    if (!empty($codeKey)) {
                        $categoryCoupons[$codeKey] = $coupon;
                    }
                }
            }
        }

        return array_values($categoryCoupons);
    }


    // public function category(Category $category): View
    //     {
    //         $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : $category->status;
    //         if ($category->status !== Status::ACTIVED && $statusValue !== 'actived') {
    //             abort(404);
    //         }

    //         $brands = resolve(BrandService::class)->getByCategory($category);
    //         $category = CategoryDetailDto::from($category);
    //         $request = request();
    //         $slug = $request->segment(count($request->segments()));

    //         $previewId = $request->query('preview_id');
    //         $previewData = null;

    //         // 1. Kiểm tra dữ liệu xem trước trong Session nếu có preview_id
    //         if ($previewId) {
    //             $possibleKeys = [
    //                 'category_preview_' . $previewId,
    //                 'category_preview_current'
    //             ];

    //             foreach ($possibleKeys as $key) {
    //                 if (session()->has($key)) {
    //                     $previewData = session()->get($key);
    //                     break;
    //                 }
    //             }
    //         }

    //         $category = null;

    //         // 2. Nếu có dữ liệu preview, khởi tạo đối tượng giả lập để hiển thị
    //         if ($previewData) {
    //             $category = new \App\Models\Category();
    //             $category->exists = true;
    //             $category->id = $previewData['id'] ?? 0;
    //             $category->name = $previewData['name'] ?? '';
    //             $category->slug = $previewData['slug'] ?? $slug;
                
    //             // Bổ sung gán giá trị type để không bị lỗi DTO
    //             $category->type = $previewData['type'] ?? \App\Enums\CategoryType::PRODUCT->value; 

    //             $category->excerpt = $previewData['excerpt'] ?? ($previewData['content'] ?? '');
    //             $category->content = $previewData['content'] ?? ($previewData['excerpt'] ?? '');
    //             $category->seo_title = $previewData['seo_title'] ?? '';
    //             $category->seo_description = $previewData['seo_description'] ?? '';
    //             $category->seo_url_canonical = $previewData['seo_url_canonical'] ?? '';
    //             $category->robots = $previewData['robots'] ?? 'index,follow';
    //         } else {
    //             // 3. Nếu không phải xem trước, truy vấn lấy danh mục thật từ DB theo slug đã bóc tách
    //             $category = \App\Models\Category::where('slug', $slug)->first();

    //             if (!$category) {
    //                 abort(404);
    //             }

    //             $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : $category->status;
    //             if ($category->status !== \App\Enums\Status::ACTIVED && $statusValue !== 'actived') {
    //                 abort(404);
    //             }
    //         }

    //         // --- Các đoạn code xử lý service, sản phẩm, phân trang bên dưới giữ nguyên ---
    //         $brands = resolve(BrandService::class)->getByCategory($category);
    //         $category = CategoryDetailDto::from($category);

    //         $parentCategory = $this->categoryService->getParent($category);
    //         $searchTargetName = $parentCategory ? $parentCategory['name'] : $category['name'];
    //         $keyword = preg_split('/( công nghiệp| cầm tay| mini| gia đình| giá rẻ)/i', $searchTargetName)[0];
            
    //         $newsCategory = \DB::table('m_categories')
    //             ->where('type', 'post')
    //             ->where(function($query) use ($keyword) {
    //                 $query->where('name', 'like', '%' . $keyword . '%');
    //             })
    //             ->first();

    //         $news = collect();
    //         if ($newsCategory) {
    //             $newsPosts = \App\Models\Post::where('status', \App\Enums\Status::ACTIVED->value)
    //                 ->whereHas('categories', function ($query) use ($newsCategory) {
    //                     $query->where('m_categories.id', $newsCategory->id);
    //                 })
    //                 ->latest('created_at')
    //                 ->limit(8)
    //                 ->get();

    //             $news = collect(\App\DTOs\Post\PostListDto::from($newsPosts))->map(function ($item) {
    //                 $item['name'] = $item['title'];
    //                 return $item;
    //             });
    //         }

    //         $brand_id   = $request->brand_id ?? null;
    //         $price      = $request->price;
    //         $children = $this->categoryService->getChildByCateId($category['id']);
    //         if (!empty($children)) {
    //             $relatedCat = $children;
    //         } else {
    //             $relatedCat = $this->categoryService->getChildByCateId($category['parent_id'] ?: $category['id']);
    //         }
    //         $cateBr = $this->categoryService->recursiveParent($category);
    //         $brand_id   = $request->brand_id ?? null;
    //         $price      = $request->price;
            
    //         $children = $this->categoryService->getChildByCateId($category['id']);
    //         if (!empty($children)) {
    //             $relatedCat = $children;
    //         } else {
    //             $relatedCat = $this->categoryService->getChildByCateId($category['parent_id'] ?: $category['id']);
                
    //             if ($category['id'] == 336 || $category['id'] == 271 || $category['id'] == 173) {
    //                 $relatedCat = []; 
    //             }
    //         }
    //         $cateBr = $this->categoryService->recursiveParent($category);

    //         foreach ($cateBr as $i)
    //             BreadcrumbHelper::add($i['name'], $i['url']);
    //         SeoHelper::setTitle($category['seoTitle']);
    //         SeoHelper::setDescription($category['seoDescription']);
    //         SeoHelper::setUrl($category['seoUrlCanonical'] ?: $category['url']);
    //         SeoHelper::setRobots($category['robots']);

    //         $parentCategory = $this->categoryService->getParent($category);

    //         $cateIds = $this->categoryService->recursiveChildId($category);
    //         $productModel = $this->productService->getList()->whereHas('categories', fn(Builder $builder) => $builder->whereIn('m_categories.id', $cateIds));

    //         if ($brand_id) {
    //             $productModel = $productModel->where('brand_id', $brand_id);
    //         }
    //         $filterPriceEnum = ProductCategoryFilterPrice::tryFrom($price);
    //         $productModel = match ($filterPriceEnum) {
    //             ProductCategoryFilterPrice::DUOI_1_TRIEU => $productModel->BetweenPrice(0, 1000000),
    //             ProductCategoryFilterPrice::TU_1_DEN_3_TRIEU => $productModel->BetweenPrice(1000000, 3000000),
    //             ProductCategoryFilterPrice::TU_3_DEN_5_TRIEU => $productModel->BetweenPrice(3000000, 5000000),
    //             ProductCategoryFilterPrice::TU_5_DEN_10_TRIEU => $productModel->BetweenPrice(5000000, 10000000),
    //             ProductCategoryFilterPrice::TU_10_DEN_15_TRIEU => $productModel->BetweenPrice(10000000, 15000000),
    //             ProductCategoryFilterPrice::TU_15_DEN_20_TRIEU => $productModel->BetweenPrice(15000000, 20000000),
    //             ProductCategoryFilterPrice::TREN_20_TRIEU => $productModel->BetweenPrice(20000000),
    //             default => $productModel
    //         };
    //         $sortEnum = ProductCategorySort::tryFrom($request->get('sort'));
    //         $productModel = match ($sortEnum) {
    //             ProductCategorySort::PRICE_ASC => $productModel->reorder()->SortPrice(true),
    //             ProductCategorySort::PRICE_DESC => $productModel->reorder()->SortPrice(false),
    //             ProductCategorySort::NEWEST => $productModel->reorder()->orderByDesc('updated_at'),
    //             ProductCategorySort::OLDEST => $productModel->reorder()->orderBy('updated_at'),
    //             default => $productModel
    //         };

    //         $allSorts = ProductCategorySort::cases();
    //         $priceSorts = array_filter($allSorts, fn($i) => in_array($i->value, ['price-asc', 'price-desc']));
    //         $otherSorts = array_filter($allSorts, fn($i) => in_array($i->value, ['new', 'old']));

    //         $priceSorts = array_values($priceSorts);
    //         $otherSorts = array_values($otherSorts);

    //         $recentIds = session()->get('recent_products', []);
    //         $viewedProducts = [];
    //         $viewedPaginate = null;

    //         if (!empty($recentIds)) {
    //             $query = \App\Models\New_Product::whereIn('id', $recentIds)
    //                 ->with(['images'])
    //                 ->where('status', \App\Enums\Status::ACTIVED->value)
    //                 ->orderByRaw('FIELD(id, ' . implode(',', $recentIds) . ')');

    //             $productsPaginated = $query->paginate(8, ['*'], 'viewed_page');
    //             $items = collect(\App\DTOs\Product\ProductListDto::from(collect($productsPaginated->items())));            
    //             $viewedProducts = $productsPaginated->setCollection($items);
    //             $viewedPaginate = $viewedProducts->links();
    //         }        

    //         $products = $productModel->paginate(16)->withQueryString();
    //         $paginate = $products->links();
    //         $total = $products->total();
    //         $products = ProductListDto::from($products);

    //         return $this->view('product/category', 
    //         compact('parentCategory', 'category', 'products', 'relatedCat', 
    //         'filterPriceEnum', 'sortEnum', 'paginate', 'brands', 'brand_id', 
    //         'total', 'price', 'cateBr', 'news', 'priceSorts', 'otherSorts', 'viewedProducts', 'viewedPaginate'));
    //     }
    public function category(Category $category): View
        {
            $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : $category->status;
            if ($category->status !== Status::ACTIVED && $statusValue !== 'actived') {
                abort(404);
            }

            $request = request();
            $slug = $request->segment(count($request->segments()));

            $previewId = $request->query('preview_id');
            $previewData = null;

            // 1. Kiểm tra dữ liệu xem trước trong Session nếu có preview_id
            if ($previewId) {
                $possibleKeys = [
                    'category_preview_' . $previewId,
                    'category_preview_current'
                ];

                foreach ($possibleKeys as $key) {
                    if (session()->has($key)) {
                        $previewData = session()->get($key);
                        break;
                    }
                }
            }

            $category = null;

            // 2. Nếu có dữ liệu preview, khởi tạo đối tượng giả lập để hiển thị
            if ($previewData) {
                $category = new \App\Models\Category();
                $category->exists = true;
                $category->id = $previewData['id'] ?? 0;
                $category->name = $previewData['name'] ?? '';
                $category->slug = $previewData['slug'] ?? $slug;
                
                // Bổ sung gán giá trị type để không bị lỗi DTO
                $category->type = $previewData['type'] ?? \App\Enums\CategoryType::PRODUCT->value; 

                $category->excerpt = $previewData['excerpt'] ?? ($previewData['content'] ?? '');
                $category->content = $previewData['content'] ?? ($previewData['excerpt'] ?? '');
                $category->seo_title = $previewData['seo_title'] ?? '';
                $category->seo_description = $previewData['seo_description'] ?? '';
                $category->seo_url_canonical = $previewData['seo_url_canonical'] ?? '';
                $category->robots = $previewData['robots'] ?? 'index,follow';
            } else {
                // 3. Nếu không phải xem trước, truy vấn lấy danh mục thật từ DB theo slug đã bóc tách
                $category = \App\Models\Category::where('slug', $slug)->first();

                if (!$category) {
                    abort(404);
                }

                $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : $category->status;
                if ($category->status !== \App\Enums\Status::ACTIVED && $statusValue !== 'actived') {
                    abort(404);
                }
            }

            // --- Các đoạn code xử lý service, sản phẩm, phân trang bên dưới giữ nguyên ---
            $category = CategoryDetailDto::from($category);

            $parentCategory = $this->categoryService->getParent($category);
            $searchTargetName = $parentCategory ? $parentCategory['name'] : $category['name'];
            $keyword = preg_split('/( công nghiệp| cầm tay| mini| gia đình| giá rẻ)/i', $searchTargetName)[0];
            
            $newsCategory = \DB::table('m_categories')
                ->where('type', 'post')
                ->where(function($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%');
                })
                ->first();

            $news = collect();
            if ($newsCategory) {
                $newsPosts = \App\Models\Post::where('status', \App\Enums\Status::ACTIVED->value)
                    ->hasThumb()
                    ->whereHas('categories', function ($query) use ($newsCategory) {
                        $query->where('m_categories.id', $newsCategory->id);
                    })
                    ->latest('created_at')
                    ->limit(5)
                    ->get();

                $news = collect(\App\DTOs\Post\PostListDto::from($newsPosts))->map(function ($item) {
                    $item['name'] = $item['title'];
                    return $item;
                });
            }

            $brand_id   = $request->brand ?? $request->brand_id ?? null;
    $price      = $request->price;
    // Ngưỡng giá do admin cấu hình riêng cho danh mục cấp 1 (m_category_price_bands) -> cả menu
    // lẫn sidebar bộ lọc đều gửi đúng 1 tham số price_range="{min}-{max}". attr = option đã chọn.
    $priceRange = (string) ($request->price_range ?? '');
    [$priceMin, $priceMax] = self::parsePriceRange($priceRange);
    $attrOption = self::parseId($request->attr ?? null);
    $cateBr     = $this->categoryService->recursiveParent($category);

            // --- XỬ LÝ LẤY VÀ PHÂN TÁCH CHUẨN XÁC DANH MỤC CON / THƯƠNG HIỆU ---
            // --- XỬ LÝ LẤY VÀ PHÂN TÁCH CHUẨN XÁC CHO $relatedCat ---
            // $children = $this->categoryService->getChildByCateId($category['id']);
            // if (!empty($children)) {
            //     $relatedCatRaw = $children;
            // } else {
            //     $relatedCatRaw = $this->categoryService->getChildByCateId($category['parent_id'] ?: $category['id']);
                
            //     if ($category['id'] == 336 || $category['id'] == 271 || $category['id'] == 173) {
            //         $relatedCatRaw = []; 
            //     }
            // }

            // $relatedCat = [];
            // --- XỬ LÝ LẤY VÀ PHÂN TÁCH CHUẨN XÁC CHO $relatedCat ---
        // 1. Xử lý phần "Chọn theo tính năng" ở phía trên
        $children = $this->categoryService->getChildByCateId($category['id']);
        
        if (!empty($children)) {
            // 1. Nếu có danh mục con -> Hiển thị danh mục con bình thường
            $relatedCatRaw = $children;
        } else {
            // 2. Nếu KHÔNG có danh mục con -> Quét các thương hiệu có chứa sản phẩm
            $brandsInCate = \App\Models\Brand::whereHas('products.categories', function($query) use ($category) {
                $query->where('m_categories.id', $category['id']);
            })->get();
            
            if ($brandsInCate->isNotEmpty()) {
                $relatedCatRaw = $brandsInCate->map(fn($brand) => [
                    'id'           => $brand->id,
                    'name'         => $brand->name ?? $brand->title,
                    'image'        => $brand->image,
                    'brand_url'    => url()->current() . '?brand=' . $brand->id,
                    'display_type' => 'brand',
                ])->toArray();
            } else {
                // Không có sản phẩm/thương hiệu thì ẩn hẳn box đi
                $relatedCatRaw = [];
            }
        }

        // Chặn ngoại lệ nếu có các ID không muốn hiển thị gì cả
        if (in_array($category['id'], [336, 271, 173])) {
            $relatedCatRaw = []; 
        }

        $relatedCat = [];

            // Hàm chuẩn hóa đường dẫn ảnh chống lỗi lặp /storage/
            $formatImageUrl = function($img) {
                if (empty($img)) return asset('images/no-image.png');
                $img = trim(str_replace(['[', ']', '"'], '', $img));
                if (empty($img)) return asset('images/no-image.png');
                
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    return $img;
                }
                
                $img = ltrim($img, '/');
                if (str_starts_with($img, 'storage/')) {
                    $img = substr($img, strlen('storage/'));
                }
                
                return asset('storage/' . $img);
            };

            if (!empty($relatedCatRaw)) {
                $catIds = array_filter(array_map(
                    fn($item) => is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null),
                    $relatedCatRaw
                ));

                //_one query_: danh mục nào có brand gắn qua pivot thì lấy tên + ảnh của brand đó
                $linkedBrands = \DB::table('category_brand_pivot')
                    ->join('brands', 'category_brand_pivot.brand_id', '=', 'brands.id')
                    ->whereIn('category_brand_pivot.category_id', $catIds)
                    ->select('category_brand_pivot.category_id', 'brands.name as b_name', 'brands.image as b_image')
                    ->get()
                    ->keyBy('category_id');

                foreach ($relatedCatRaw as $item) {
                    $isDataArray = is_array($item);
                    $catId = $isDataArray ? ($item['id'] ?? null) : ($item->id ?? null);
                    $newItem = $isDataArray ? $item : (array) $item;
                    $linkedBrand = $linkedBrands->get($catId);

                    if ($linkedBrand) {
                        $newItem['name'] = $linkedBrand->b_name ?? $newItem['name'];
                        $newItem['image'] = $formatImageUrl($linkedBrand->b_image ?? '');
                        $newItem['display_type'] = 'brand';
                    } else {
                        $newItem['image'] = $formatImageUrl($newItem['image'] ?? '');
                        $newItem['display_type'] = $newItem['display_type'] ?? 'attribute';
                    }

                    $relatedCat[] = $newItem;
                }
            }

            SeoHelper::setTitle($category['seoTitle']);
            SeoHelper::setDescription($category['seoDescription']);
            SeoHelper::setUrl($category['seoUrlCanonical'] ?: $category['url']);
            SeoHelper::setRobots($category['robots']);

            $cateIds = $this->categoryService->recursiveChildId($category);
    $productModel = $this->productService->getList()->whereHas('categories', fn(Builder $builder) => $builder->whereIn('m_categories.id', $cateIds));

    if ($brand_id) {
        // Kiểm tra xem $brand_id truyền lên là số (ID) hay chuỗi (slug) để query cho chính xác
        if (is_numeric($brand_id)) {
            $productModel = $productModel->where('brand_id', $brand_id);
        } else {
            $productModel = $productModel->whereHas('brand', function($q) use ($brand_id) {
                $q->where('slug', $brand_id);
            });
        }
    }
            $filterPriceEnum = ProductCategoryFilterPrice::tryFrom($price);
            $productModel = match ($filterPriceEnum) {
                ProductCategoryFilterPrice::DUOI_1_TRIEU => $productModel->BetweenPrice(0, 1000000),
                ProductCategoryFilterPrice::TU_1_DEN_3_TRIEU => $productModel->BetweenPrice(1000000, 3000000),
                ProductCategoryFilterPrice::TU_3_DEN_5_TRIEU => $productModel->BetweenPrice(3000000, 5000000),
                ProductCategoryFilterPrice::TU_5_DEN_10_TRIEU => $productModel->BetweenPrice(5000000, 10000000),
                ProductCategoryFilterPrice::TU_10_DEN_15_TRIEU => $productModel->BetweenPrice(10000000, 15000000),
                ProductCategoryFilterPrice::TU_15_DEN_20_TRIEU => $productModel->BetweenPrice(15000000, 20000000),
                ProductCategoryFilterPrice::TREN_20_TRIEU => $productModel->BetweenPrice(20000000),
                default => $productModel
            };

            // Mức giá cấu hình riêng cho danh mục: cộng dồn với bộ lọc enum cũ.
            if ($priceMin !== null || $priceMax !== null) {
                $productModel = $productModel->BetweenPrice($priceMin, $priceMax);
            }

            // quan hệ attributeOptions() của New_Product sinh SQL sai cột nên phải lọc thẳng qua pivot.
            if ($attrOption !== null) {
                $productModel = $productModel->whereIn(
                    'm_products.id',
                    \DB::table('m_product_attributes')->where('option_id', $attrOption)->pluck('product_id')
                );
            }

            $sortEnum = ProductCategorySort::tryFrom($request->get('sort'));
            $productModel = match ($sortEnum) {
                ProductCategorySort::PRICE_ASC => $productModel->reorder()->SortPrice(true),
                ProductCategorySort::PRICE_DESC => $productModel->reorder()->SortPrice(false),
                ProductCategorySort::NEWEST => $productModel->reorder()->orderByDesc('updated_at'),
                ProductCategorySort::OLDEST => $productModel->reorder()->orderBy('updated_at'),
                default => $productModel
            };

            $allSorts = ProductCategorySort::cases();
            $priceSorts = array_filter($allSorts, fn($i) => in_array($i->value, ['price-asc', 'price-desc']));
            $otherSorts = array_filter($allSorts, fn($i) => in_array($i->value, ['new', 'old']));

            $priceSorts = array_values($priceSorts);
            $otherSorts = array_values($otherSorts);

            // Nút "Xem thêm" tải tiếp 20 sản phẩm/lần, số trên nút = số còn lại của lần tải kế tiếp
            $perPage = 20;
            $productsPage = $productModel->paginate($perPage)->withQueryString();
            $total       = $productsPage->total();
            $nextPageUrl = $productsPage->nextPageUrl();
            $loadMoreCount = min($perPage, max(0, $total - ($productsPage->lastItem() ?? 0)));
            $products    = ProductListDto::from($productsPage);

            // Các thương hiệu thực sự có sản phẩm trong danh mục này, ưu tiên DQC-Tech rồi Kumisai
            $brandIds = \App\Models\New_Product::whereHas('categories', fn(Builder $builder) => $builder->where('m_categories.id', $category['id']))
                            ->where('brand_id', '>', 0)
                            ->distinct()
                            ->pluck('brand_id');

            $priority = ['DQC-Tech' => 1, 'Kumisai' => 2];
            $brandCats = \App\Models\Brand::whereIn('id', $brandIds)
                            ->where('is_active', 1)
                            ->get()
                            ->sortBy([
                                fn($a, $b) => ($priority[trim($a->name)] ?? 999) <=> ($priority[trim($b->name)] ?? 999),
                                fn($a, $b) => strcmp(trim($a->name), trim($b->name)),
                            ])
                            ->values();


            // Ngưỡng giá hiển thị ở bộ lọc, cấu hình theo danh mục cấp 1 cao nhất.
            // Cột "Thông số" chỉ render ở menu/sidebar, không nằm trong khối lọc của trang.
            $rootId       = $cateBr[0]['id'] ?? $category['id'];
            $filterConfig = $this->categoryService->getFilterConfig();
            $priceBands   = $filterConfig[$rootId]['price'] ?? [];

            return $this->view('product/category', 
            compact('parentCategory', 'category', 'products', 'relatedCat', 'brandCats',
            'filterPriceEnum', 'sortEnum', 'nextPageUrl', 'brand_id', 'loadMoreCount',
            'total', 'price', 'cateBr', 'news', 'priceSorts', 'otherSorts',
            'priceBands', 'priceRange', 'priceMin', 'priceMax', 'attrOption'));
        }

    /** "0-3000000" / "3000000-" -> [min, max]; max rỗng = không chặn trên. */
    private static function parsePriceRange(string $range): array
    {
        if ($range === '' || !str_contains($range, '-')) {
            return [null, null];
        }

        [$min, $max] = explode('-', $range, 2);

        return [self::parseId($min), self::parseId($max)];
    }

    private static function parseId(string|null $value): ?int
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }

    public function recentProducts($product){
        $recentProducts = session()->get('recent_products', []);
        if (($key = array_search($product->id, $recentProducts)) !== false) {
            unset($recentProducts[$key]);
        }
        array_unshift($recentProducts, $product->id);
        $recentProducts = array_slice($recentProducts, 0, 20);
        session(['recent_products' => $recentProducts]);
    }

    public function viewAllRecent()
    {
        $recentIds = session()->get('recent_products', []);
        if (empty($recentIds)) return redirect('/');
        
        $meta_title = 'Sản phẩm bạn đã xem gần đây';
        $meta_desc = 'Danh sách các sản phẩm bạn đã xem qua tại hệ thống. Xem lại thông tin chi tiết và đặt mua nhanh chóng với giá ưu đãi.';
        
        if (class_exists('\App\Helpers\SeoHelper')) {
            \App\Helpers\SeoHelper::setTitle($meta_title);
            \App\Helpers\SeoHelper::setDescription($meta_desc);
        }

        $products = \App\Models\New_Product::whereIn('id', $recentIds)
            ->with(['images'])
            ->where('status', \App\Enums\Status::ACTIVED->value)
            ->orderByRaw('FIELD(id, ' . implode(',', $recentIds) . ')')
            ->get();

        $productsDto = \App\DTOs\Product\ProductListDto::from($products);

        $finalProducts = collect($productsDto)->map(function($item, $index) use ($products) {
            $productModel = $products[$index];
            $firstImage = $productModel->images->where('has_primary', 1)->first() 
                        ?? $productModel->images->first();

            $item['imago'] = $firstImage ? $firstImage->url : ''; 
            $item['image'] = $item['imago'];
            
            return $item;
        })->toArray();

        return $this->view('product/recent-view-all', [
            'products' => $finalProducts
        ]);
    }

    public function viewAllRelated()
    {
        // 1. Nhận đúng product_id từ request URL găm sang
        $productId = request()->get('product_id');
        if (!$productId) {
            return redirect('/');
        }

        // 2. Tìm model gốc sản phẩm để lấy tên làm tiêu đề trang hiển thị
        $productModel = \App\Models\New_Product::find($productId);
        if (!$productModel) {
            return redirect('/');
        }

        $meta_title = 'Sản phẩm liên quan đến: ' . $productModel->name;
        $meta_desc = 'Danh sách các sản phẩm chất lượng liên quan đến ' . $productModel->name . '. Mua ngay với giá ưu đãi tốt nhất.';
        
        if (class_exists('\App\Helpers\SeoHelper')) {
            \App\Helpers\SeoHelper::setTitle($meta_title);
            \App\Helpers\SeoHelper::setDescription($meta_desc);
        }

        // 3. Truy vấn trực tiếp từ DB lấy danh mục con trực tiếp gắn liền (Bẻ gãy cache)
        $categoryIds = \Illuminate\Support\Facades\DB::table('m_category_product')
            ->where('product_id', $productId)
            ->pluck('category_id')
            ->toArray();

        // Nếu sản phẩm không được phân loại, trả về view trống an toàn
        if (empty($categoryIds)) {
            return $this->view('product/related-view-all', [
                'products'       => [],
                'currentProduct' => $productModel,
                'paginate'       => null
            ]);
        }

        // 4. SỬA LỖI SQL: Dùng select thuần để tránh lỗi sinh chuỗi rỗng `()`.`id` khi phân trang
        $query = \App\Models\New_Product::query()
            ->with(['images']) 
            ->where('status', \App\Enums\Status::ACTIVED->value)
            ->whereIn('id', function ($subQuery) use ($categoryIds, $productId) {
                $subQuery->select('product_id')
                    ->from('m_category_product')
                    ->whereIn('category_id', $categoryIds)
                    ->where('product_id', '!=', $productId);
            })
            ->orderBy('order', 'desc')
            ->orderBy('regular_price', 'desc');

        // 5. Tiến hành phân trang 20 item giống cấu trúc View của bạn có thành phần {!! $paginate !!}
        $productsPaginator = $query->paginate(20)->withQueryString();

        // 6. MAP DỮ LIỆU ẢNH trực tiếp trên Model Collection trước khi chuyển đổi DTO
        // Việc này đảm bảo ảnh luôn đi kèm đúng ID sản phẩm, không bị lệch index
        $productsPaginator->getCollection()->transform(function ($productModel) {
            $firstImage = $productModel->images->where('has_primary', 1)->first() 
                        ?? $productModel->images->first();
            
            // Gán tạm vào model để DTO hoặc logic phía sau lấy được
            $productModel->temp_image = $firstImage ? $firstImage->url : '';
            return $productModel;
        });

        // 7. Chuyển đổi sang DTO
        $productsDto = \App\DTOs\Product\ProductListDto::from($productsPaginator);
        
        $finalProducts = collect($productsDto)->map(function($item, $index) use ($productsPaginator) {
            $model = $productsPaginator->items()[$index] ?? null;
            $item['image'] = $model->temp_image ?? '';
            $item['imago'] = $item['image'];
            return $item;
        })->toArray();

        // 8. Lấy chuỗi HTML phân trang để đổ ra {!! $paginate !!} ở View
        $paginate = $productsPaginator->links();

        // 9. Trả về đúng view cấu trúc đường dẫn thư mục của bạn
        $responseHtml = $this->view('product/related-view-all', [
            'products'       => $finalProducts,
            'currentProduct' => $productModel,
            'paginate'       => $paginate
        ])->render();

        // 10. Trả về response kèm Header chống cache triệt để
        return response($responseHtml)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function preview(Request $request, $type)
    {
        if ($type !== \App\Enums\CategoryType::PRODUCT->value) {
            return redirect()->back()->with('error', 'Chức năng xem trước chỉ áp dụng cho danh mục sản phẩm.');
        }

        // Tạo một key session riêng theo thời gian hoặc ID đang sửa
        $previewId = $request->input('id', 'new_' . time());
        $previewSessionKey = 'category_preview_' . $previewId;

        // Lưu toàn bộ dữ liệu đang gõ trên form vào session
        session()->put($previewSessionKey, $request->all());

        // Lấy slug người dùng vừa nhập để tạo đường dẫn frontend
        $slug = $request->input('slug', 'preview-category');

        // Redirect sang trang frontend kèm query param preview_id
        return redirect()->to(route('frontend.slug', ['slug' => $slug]) . '?preview_id=' . $previewId);
    }


}
