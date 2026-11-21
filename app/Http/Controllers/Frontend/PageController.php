<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Post\PostDetailDto;
use App\DTOs\Product\ProductListDto;
use App\Enums\PostType;
use App\Enums\ProductCategoryFilterPrice;
use App\Enums\ProductCategorySort;
use App\Enums\Status;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use App\Models\Brand;
use App\Models\Post;
use App\Services\FeServices\PostService;
use App\Services\FeServices\ProductService;
use App\Services\FeServices\SettingFieldService;
use App\Services\RequestService;
use Illuminate\Http\Request;

class PageController extends BaseController
{
    public function __construct(
        protected PostService $postService,
        protected ProductService $productService,
        protected SettingFieldService $settingFieldService
    ) {
        parent::__construct();
    }
    public function slug($slug)
    {
        $page = Post::where('slug', $slug)
            ->where('type', PostType::PAGE->value)
            ->first();
        if ($page == null) {
            return redirect()->route('frontend.page.home');
        }
        $statusValue = is_object($page->status) ? ($page->status->value ?? $page->status) : $page->status;
        if ($page->status !== Status::ACTIVED && $statusValue !== 'actived') {
            abort(404);
        }

        $posts = $this->postService->getLastPost($slug);
        $lastProducts = $this->productService->getLastProducts();

        SeoHelper::setTitle($page->seo_title ? $page->seo_title : $page->title);
        SeoHelper::setDescription($page->seo_description ?? '');
        SeoHelper::setKeywords($page->seo_keywords ?? '');
        SeoHelper::setUrl($page->seo_url_canonical ? $page->seo_url_canonical : route('frontend.slug', ['slug' => $page->slug]));

        $page = PostDetailDto::from($page);
        $viewName = 'web.page.index';

        if ($slug == 'lien-he') {
            $viewName = 'web.page.contact';
            $googleMap = $this->settingFieldService->getKey('google_map');
        }

        return view($viewName, [
            'page' => $page,
            'posts' => $posts,
            'lastProducts' => $lastProducts,
            'googleMap' => isset($googleMap) ? $googleMap : null,
        ]);
    }

    public function search(Request $request)
    {
        $request = resolve(RequestService::class)->request;
        $price   = $request->price;
        $keyword = $request->query('s');
        $brand_id = $request->brand ?? $request->brand_id ?? null;

        if ($keyword == null) {
            return redirect()->route('frontend.page.home');
        }
        BreadcrumbHelper::add('Tìm kiếm sản phẩm', 'tim-kiem');
        SeoHelper::setTitle('Tìm kiếm sản phẩm');
        $productModel = $this->productService->getSearchQuery($keyword);

        // Danh sách thương hiệu cho bộ lọc: lấy từ toàn bộ kết quả của từ khóa, không phụ thuộc bộ lọc đang chọn
        $brandIds = $this->productService->getSearchQuery($keyword)
            ->where('brand_id', '>', 0)
            ->reorder()
            ->distinct()
            ->pluck('brand_id');
        $priority = ['DQC-Tech' => 1, 'Kumisai' => 2];
        $brandCats = Brand::whereIn('id', $brandIds)
            ->where('is_active', 1)
            ->get()
            ->sortBy([
                fn($a, $b) => ($priority[trim($a->name)] ?? 999) <=> ($priority[trim($b->name)] ?? 999),
                fn($a, $b) => strcmp(trim($a->name), trim($b->name)),
            ])
            ->values();

        if ($brand_id) {
            if (is_numeric($brand_id)) {
                $productModel = $productModel->where('brand_id', $brand_id);
            } else {
                $productModel = $productModel->whereHas('brand', fn($q) => $q->where('slug', $brand_id));
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
        $sortEnum = ProductCategorySort::tryFrom($request->get('sort'));
        $productModel = match ($sortEnum) {
            ProductCategorySort::PRICE_ASC => $productModel->reorder()->SortPrice(true),
            ProductCategorySort::PRICE_DESC => $productModel->reorder()->SortPrice(false),
            ProductCategorySort::NEWEST => $productModel->reorder()->orderByDesc('updated_at'),
            ProductCategorySort::OLDEST => $productModel->reorder()->orderBy('updated_at'),
            default => $productModel
        };

        $priceSorts = array_values(array_filter(ProductCategorySort::cases(), fn($i) => in_array($i->value, ['price-asc', 'price-desc'])));

        // Nút "Xem thêm" tải tiếp 20 sản phẩm/lần, số trên nút = số còn lại của lần tải kế tiếp
        $perPage = 20;
        $productsPage = $productModel->paginate($perPage)->withQueryString();
        $total = $productsPage->total();
        $nextPageUrl = $productsPage->nextPageUrl();
        $loadMoreCount = min($perPage, max(0, $total - ($productsPage->lastItem() ?? 0)));
        $products = ProductListDto::from($productsPage);

        // 2. Tìm tin tức / bài viết bằng PostService
        $posts = resolve(PostService::class)->search($keyword, 10);
        $totalPosts = count($posts);
        

        return $this->view('page/search', compact('products', 'posts', 'keyword', 'filterPriceEnum', 'sortEnum', 'nextPageUrl', 'brand_id', 'loadMoreCount', 'brandCats', 'priceSorts', 'total', 'totalPosts', 'price'));
    }

    public  function getWarranty()
    {
        SeoHelper::setTitle('Tra cứu bảo hành sản phẩm');

        return view('web.page.warranty');
    }
}