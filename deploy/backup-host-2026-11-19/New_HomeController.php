<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\ArrayHelper;
use App\Helpers\GetFeUrlHelper;
use App\Helpers\SeoHelper;
use App\Services\FeServices\BannerService;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\PostService;
use App\Services\FeServices\ProductService;
use App\Services\FeServices\ReviewService;
use App\Services\FeServices\SettingService;
use App\Services\FeServices\VideoService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class New_HomeController extends BaseController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly ProductService $productService,
        private readonly BannerService $bannerService,
        private readonly SettingService $settingService,
        private readonly VideoService $videoService,
        private readonly PostService $postService,
        private readonly ReviewService $reviewService
    )
    {
        parent::__construct();
    }
    public function index(): View
    {
        $saleProducts = $this->productService->getProductsSale(); 
        $hotProducts = $this->productService->getProductsHot(); 
        $bannersTop = $this->bannerService->bannerByPosition(0);
        $bannersMid = $this->bannerService->bannerByPosition(1);
        $catHome = array_filter($this->categoryService->trees(), fn($i) => !$i['parent_id'] && $i['is_home_page']);
        $cateHomeIds = array_column($catHome, 'id');
        $productByCate = $this->productService->getByCateParents($cateHomeIds, 16);
        $catHome = array_map(function ($i) use(&$productByCate) {
            $i['products'] = ArrayHelper::pull($productByCate, fn($iPro) => $iPro['category_id'] == $i['id']);
            return $i;
        }, $catHome);
        $settings = $this->settingService->all();

        $categoriesParent = $this->categoryService->getProductCategoriesParent();
        $categoriesParent = array_values($categoriesParent);
        $half = count($categoriesParent) / 2;
        $groupedCategoriesParent = [];
        for ($i = 0; $i < $half; $i++) {
            $groupedCategoriesParent[] = [
                $categoriesParent[$i],          
                $categoriesParent[$i + $half]
            ];
        }
        $videos = $this->videoService->getVideos();
        $posts = $this->postService->getPosts(8);
        $reviews = $this->reviewService->all();
    
        SeoHelper::setTitle($settings['site_tite']);
        SeoHelper::setDescription($settings['site_description']);
        SeoHelper::setKeywords($settings['site_keywords']);
        SeoHelper::setUrl(GetFeUrlHelper::home());

        return $this->view('home.index', compact('saleProducts','hotProducts', 'bannersTop', 'bannersMid', 'catHome', 'settings', 'groupedCategoriesParent', 'videos', 'posts', 'reviews'));
    }
}
