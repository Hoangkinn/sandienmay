<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Post\PostListDto;
use App\Enums\PostType;
use App\Enums\Status;
use App\Helpers\AgentHelper;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\FrontendController;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Traits\CategoryTrait;
use App\Traits\SettingTrait;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class CategoryController extends BaseController
{
    use SettingTrait;
    use CategoryTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function slug(Category $category): View
    // {
    //     // Kiểm tra nếu danh mục đã bị ẩn (status không phải ACTIVED) -> Trả về 404 ngay
    //     $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : $category->status;
    //     if ($category->status !== Status::ACTIVED && $statusValue !== 'actived') {
    //         abort(404);
    //     }
        
    //     $settings = $this->getSettings();

    //     SeoHelper::setTitle($category->seo_title ? $category->seo_title : $category->name);
    //     SeoHelper::setDescription($category->seo_description ?? '');
    //     SeoHelper::setKeywords($category->seo_keywords ?? '');
    //     SeoHelper::setUrl($category->seo_url_canonical ? $category->seo_url_canonical : route('frontend.slug', ['slug' => $category->slug]));

    //     $allCategories = \App\Models\Category::where('type', PostType::POST->value)
    //         ->withCount('posts')
    //         ->get()
    //         ->keyBy('id') 
    //         ->toArray();

    //     $categoriesRaw = $this->getCategoriesPost(); 
    //     $categoriesParent = array_values($categoriesRaw);
        
    //     foreach ($categoriesParent as &$cat) {
    //         $cat['posts_count'] = $allCategories[$cat['id']]['posts_count'] ?? 0;
    //     }
    //     unset($cat);

    //     $half = count($categoriesParent) / 2;
    //     $groupedCategoriesParent = [];
    //     for ($i = 0; $i < $half; $i++) {
    //         $groupedCategoriesParent[] = [
    //             $categoriesParent[$i],
    //             $categoriesParent[$i + $half]
    //         ];
    //     }

    //     $postModel = $category->posts2()->where('status', Status::ACTIVED->value);
    //     $posts = $postModel->paginate(20);
    //     BreadcrumbHelper::add('Tin tức', route('frontend.post.index'));
    //     BreadcrumbHelper::add($category->name, route('frontend.slug', $category->slug));

    //     return view('web/post/category' , [
    //         'posts' => PostListDto::from($posts),
    //         'settings' => $settings,
    //         'categories' => $groupedCategoriesParent,
    //         'paginate' => $posts->withQueryString()->links(),
    //         'category' => $category
    //     ]);
    // }
    public function slug($slug, $request = null): View
{
    $request = ($request instanceof \Illuminate\Http\Request) ? $request : request();
    
    // Nếu Laravel Route Model Binding đã tự động bind $slug thành object Category thì lấy luôn, ngược lại mới query theo string
    if ($slug instanceof Category) {
        $category = $slug;
    } else {
        $category = Category::where('slug', $slug)->first();
    }

    if ($category == null) {
        $slugString = is_object($slug) ? ($slug->slug ?? '') : $slug;
        $category = new Category();
        $category->slug = $slugString;
    }

    // --- KIỂM TRA TRẠNG THÁI VÀ SESSION PREVIEW ---
    $previewId = $request->query('preview_id');
    $previewSessionKey = $previewId ? ('category_preview_' . ($category->id ?? $previewId)) : null;
    $isPreviewing = $previewSessionKey && session()->has($previewSessionKey);

    $statusValue = is_object($category->status) ? ($category->status->value ?? $category->status) : ($category->status ?? 'actived');

    if (!$isPreviewing && $category->exists && $category->status !== Status::ACTIVED && $statusValue !== 'actived') {
        abort(404);
    }

    // --- LẤY DỮ LIỆU TẠM TỪ SESSION NẾU ĐANG XEM TRƯỚC ---
    if ($isPreviewing) {
        $previewData = session()->get($previewSessionKey);
        session()->forget($previewSessionKey);
        
        if ($previewData) {
            $category->name = $previewData['name'] ?? $category->name;
            $category->slug = $previewData['slug'] ?? $category->slug;
            $category->excerpt = $previewData['excerpt'] ?? $category->excerpt;
            $category->seo_title = $previewData['seo_title'] ?? $category->seo_title;
            $category->seo_keywords = $previewData['seo_keywords'] ?? $category->seo_keywords;
            $category->seo_description = $previewData['seo_description'] ?? $category->seo_description;
            $category->seo_url_canonical = $previewData['seo_url_canonical'] ?? $category->seo_url_canonical;
        }
    }

    $settings = $this->getSettings();

    SeoHelper::setTitle($category->seo_title ?: ($category->name ?: 'Danh mục'));
    SeoHelper::setDescription($category->seo_description ?? '');
    SeoHelper::setKeywords($category->seo_keywords ?? '');
    SeoHelper::setUrl($category->seo_url_canonical ? $category->seo_url_canonical : route('frontend.slug', ['slug' => $category->slug ?? '']));
    SeoHelper::setRobots(!$category->seo_no_index ? 'index, follow' : 'noindex, nofollow');

    $allCategories = \App\Models\Category::where('type', PostType::POST->value)
        ->withCount('posts')
        ->get()
        ->keyBy('id') 
        ->toArray();

    $categoriesRaw = $this->getCategoriesPost(); 
    $categoriesParent = array_values($categoriesRaw);
    foreach ($categoriesParent as &$cat) {
        $cat['posts_count'] = $allCategories[$cat['id']]['posts_count'] ?? 0;
    }
    unset($cat);

    $half = count($categoriesParent) / 2;
    $groupedCategoriesParent = [];
    for ($i = 0; $i < $half; $i++) {
        $groupedCategoriesParent[] = [
            $categoriesParent[$i],
            $categoriesParent[$i + $half]
        ];
    }

    $isDataExists = $category->exists && $category->id;
    
    $posts = $isDataExists 
        ? $category->posts2()->where('m_posts.status', Status::ACTIVED->value)
            ->hasThumb()
            ->orderByDesc('m_posts.order')->orderByDesc('m_posts.created_at')->paginate(15) 
        : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

    // BreadcrumbHelper::add('Tin tức', route('frontend.post.index'));
    BreadcrumbHelper::add($category->name ?? 'Chi tiết danh mục', '#');

    return view('web/post/category', [
        'posts' => PostListDto::from($posts),
        'settings' => $settings,
        'categories' => $groupedCategoriesParent,
        'paginate' => $isDataExists ? $posts->withQueryString()->links() : '',
        'nextPageUrl' => $isDataExists ? $posts->nextPageUrl() : null,
        'category' => $category
    ]);
}
}
