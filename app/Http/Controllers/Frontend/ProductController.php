<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Product\ProductListDto;
use App\Enums\CommentStatus;
use App\Enums\CommentType;
use App\Enums\ProductCategoryFilterPrice;
use App\Enums\ProductCategorySort;
use App\Enums\Status;
use App\Helpers\AgentHelper;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\FrontendController;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\FeServices\BannerService;
use App\Services\FeServices\BrandService;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\CommentService;
use App\Services\FeServices\ProductService;
use App\Services\RequestService;
use Jenssegers\Agent\Agent;

class ProductController extends BaseController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly CategoryService $categoryService
    ) {
        parent::__construct();
    }
    public function loadProducts($id)
    {
        $products = $this->productService->getByCategory($id);

        $html = view('components.fe.products', [
            'products' => $products,
            'preload' => false,
            'viewCart' => false,
            'viewSoldQuantity' => true,
            'quickView' => true
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function getAll()
    {
        $request = resolve(RequestService::class)->request;
        $price   = $request->price;
        BreadcrumbHelper::add('Sản phẩm', 'san-pham');
        SeoHelper::setTitle('Tất cả sản phẩm');
        $productModel = $this->productService->getList();
        $relatedCat = $this->categoryService->getProductCategoriesParent();;
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

        $products = $productModel->paginate(20)->withQueryString();
        $paginate = $products->links();
        $total = $products->total();
        $products = ProductListDto::from($products);

        return $this->view('product/index', compact('products', 'relatedCat', 'filterPriceEnum', 'sortEnum', 'paginate', 'total', 'price'));
    }

    public function compare(string $ids)
    {
        $ids = explode('-', $ids);
        $products = resolve(ProductService::class)->getByIds($ids, true);
        if (! in_array($products->count(), [2,3]))
            abort(404);
        
        $reviewService = resolve(CommentService::class);
        foreach ($products as $product) {
            $product->attributes = $product->getArrProductAttributes();
            $product->reviewAvg = $reviewService->getReviewStatsByProduct($product)['avg'];
        }

        $attributeList = $products->pluck('attributes')->toArray(); 
        $keys = [];
        foreach ($attributeList as $attributes) {
            $keys = array_merge($keys, array_keys($attributes));
        }
        $keys = array_values(array_unique($keys));
        $reviewAvgs = $products->pluck('reviewAvg')->toArray();

        $products = ProductListDto::from($products);

        SeoHelper::setRobots('noindex, nofollow');
        SeoHelper::setTitle('So sánh sản phẩm');
        BreadcrumbHelper::add('So sánh sản phẩm', 'so-sanh-san-pham');
        return $this->view('product/compare', compact('products', 'attributeList', 'keys', 'reviewAvgs'));
    }
}
