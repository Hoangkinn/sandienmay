<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Post\PostDetailDto;
use App\DTOs\Post\PostListDto;
use App\DTOs\Product\ProductListDto;
use App\Enums\CategoryType;
use App\Enums\CommentStatus;
use App\Enums\CommentType;
use App\Enums\PostType;
use App\Enums\Status;
use App\Helpers\AgentHelper;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\FrontendController;
use App\Models\Comment;
use App\Models\New_Product;
use App\Models\Post;
use App\Models\Product;
use App\Services\FeServices\PostService;
use App\Services\FeServices\ProductService;
use App\Services\FeServices\SettingFieldService;
use App\Traits\CategoryTrait;
use App\Traits\SettingTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;

class PostController extends BaseController
{
    use CategoryTrait;
    use SettingTrait;
    public function __construct(
        protected PostService $postService,
        protected ProductService $productService
    ) {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = $this->getSettings();

        SeoHelper::setTitle($settings[2]['setting_value']);
        SeoHelper::setDescription($settings[4]['setting_value']);
        SeoHelper::setKeywords($settings[3]['setting_value']);
        SeoHelper::setUrl(route('frontend.post.index'));

        // Lấy số trang hiện tại từ request (?page=1, ?page=2...)
        $page = request()->get('page', 1);
        $cacheKey = 'frontend_posts_index_page_v8_' . $page;

        // Cache trong 24 giờ (86400 giây)
        $cachedData = Cache::remember($cacheKey, 86400, function () {
            $limit = 10;

            // 1. Lấy bài nổi bật
            $featuredPosts = Post::where('status', Status::ACTIVED->value)
                ->where('type', PostType::POST->value)
                ->where('is_featured', 1)
                ->hasThumb()
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            // 2. Lấy bài bù nếu thiếu
            if ($featuredPosts->count() < $limit) {
                $needed = $limit - $featuredPosts->count();

                $latestPosts = Post::where('status', Status::ACTIVED->value)
                    ->where('type', PostType::POST->value)
                    ->where('is_featured', 0)
                    ->hasThumb()
                    ->whereNotIn('id', $featuredPosts->pluck('id'))
                    ->orderByDesc('created_at')
                    ->limit($needed)
                    ->get();

                $featuredPosts = $featuredPosts->concat($latestPosts);
            }

            // 3. Phân trang bài viết
            $posts = Post::orderByDesc('order')
                ->orderByDesc('created_at')
                ->where('status', Status::ACTIVED->value)
                ->where('type', PostType::POST->value)
                ->hasThumb()
                ->paginate(15)
                ->withQueryString();

            // 4. Lấy sản phẩm mới nhất
            $lastProducts = Product::where('status', Status::ACTIVED->value)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // 5. Lấy danh mục
            $allCategories = \App\Models\Category::where('type', PostType::POST->value)
                ->withCount('posts')
                ->get()
                ->keyBy('id'); // Bỏ toArray() để giữ lại Eloquent Collection/Model

            $categoriesRaw = $this->getCategoriesPost();
            $categoriesParent = array_values($categoriesRaw);

            foreach ($categoriesParent as &$cat) {
                $catId = $cat['id'] ?? null;
                $categoryModel = $allCategories[$catId] ?? null;

                $cat['posts_count'] = $categoryModel ? $categoryModel->posts_count : 0;
                $cat['slug_real'] = $categoryModel ? $categoryModel->slug : ''; // Lấy chuẩn slug từ model

                $categoryPosts = $categoryModel ? $categoryModel->posts2()
                    ->where('m_posts.status', Status::ACTIVED->value)
                    ->whereNotNull('m_posts.image')
                    ->where('m_posts.image', '<>', '')
                    ->latest()
                    ->limit(4)
                    ->get() : collect();
                $cat['posts'] = PostListDto::from($categoryPosts);
            }
            unset($cat);

            $half = count($categoriesParent) / 2;
            $groupedCategoriesParent = [];
            for ($i = 0; $i < $half; $i++) {
                $groupedCategoriesParent[] = [
                    $categoriesParent[$i],
                    $categoriesParent[$i + $half] ?? null
                ];
            }

            return [
                'paginate' => $posts->links()->toHtml(),
                'nextPageUrl' => $posts->nextPageUrl(),
                'posts' => PostListDto::from($posts),
                'categories' => $groupedCategoriesParent,
                'lastProducts' => $lastProducts,
                'featuredPosts' => $featuredPosts,
            ];
        });

        return $this->view('post.index', [
            'paginate' => $cachedData['paginate'],
            'nextPageUrl' => $cachedData['nextPageUrl'],
            'posts' => $cachedData['posts'],
            'settings' => $settings,
            'categories' => $cachedData['categories'],
            'lastProducts' => $cachedData['lastProducts'],
            'featuredPosts' => $cachedData['featuredPosts'],
        ]);
    }

    /**
     * Tải tiếp bài viết cho nút "Xem thêm" (AJAX, không đổi URL).
     */
    public function loadMore(Request $request)
    {
        $after = max(0, (int) $request->input('after', 15));
        $limit = 20;

        if ($categorySlug = $request->input('category')) {
            $category = \App\Models\Category::where('slug', $categorySlug)
                ->where('type', PostType::POST->value)
                ->first();

            if (!$category) {
                return response()->json(['error' => true, 'message' => 'Danh mục không tồn tại'], 404);
            }

            $query = $category->posts2();
        } else {
            $query = Post::query();
        }

        $query = $query
            ->where('m_posts.status', Status::ACTIVED->value)
            ->where('m_posts.type', PostType::POST->value)
            ->hasThumb()
            ->orderByDesc('m_posts.order')
            ->orderByDesc('m_posts.created_at');

        $total = (clone $query)->count();
        $posts = PostListDto::from($query->select('m_posts.*')->skip($after)->take($limit)->get());

        return response()->json([
            'error' => false,
            'html' => view('web.post.partials.cards', ['posts' => $posts])->render(),
            'count' => count($posts),
            'left' => max(0, $total - $after - count($posts)),
            'hasMore' => ($after + count($posts)) < $total,
        ]);
    }

    public function slug($slug)
    {
        $post = Post::where('slug', $slug)
            ->where('type', PostType::POST->value)
            ->first();
            
        // Nếu không tồn tại bài viết -> Trả về lỗi 404 luôn
        if ($post == null) {
            abort(404);
        }

        // --- KIỂM TRA TRẠNG THÁI: Nếu bài viết đã bị ẩn (status != ACTIVED) ---
        $previewSessionKey = 'post_preview_' . $post->id;
        $isPreviewing = session()->has($previewSessionKey);

        $statusValue = is_object($post->status) ? ($post->status->value ?? $post->status) : $post->status;
        
        // Nếu không phải đang xem trước (preview) VÀ trạng thái khác ACTIVED -> Trả về 404
        if (!$isPreviewing && $post->status !== Status::ACTIVED && $statusValue !== 'actived') {
            abort(404);
        }

        // --- KIỂM TRA VÀ LẤY DỮ LIỆU XEM TRƯỚC TỪ ADMIN ---
        if ($isPreviewing) {
            $previewData = session()->get($previewSessionKey);
            
            // Xóa ngay lập tức session sau khi lấy để bấm "Quay lại" hoặc F5 không bị kẹt bản cũ
            // session()->forget($previewSessionKey);
            
            // Gán đè tạm dữ liệu vừa gõ ở form admin vào object post để hiển thị ra giao diện
            $post->title = $previewData['title'] ?? $post->title;
            $post->slug = $previewData['slug'] ?? $post->slug;
            $post->excerpt = $previewData['excerpt'] ?? $post->excerpt;
            $post->content = $previewData['content'] ?? $post->content;
            $post->seo_title = $previewData['seo_title'] ?? $post->seo_title;
            $post->seo_keywords = $previewData['seo_keywords'] ?? $post->seo_keywords;
            $post->seo_description = $previewData['seo_description'] ?? $post->seo_description;
        } else {
            // Nếu không phải đang xem trước thì mới tăng lượt view thật như bình thường
            $viewSave = $post;
            $viewSave->count_view += 1;
            $viewSave->timestamps = false;
            $viewSave->save();
        }

        $lastProducts = $this->productService->getLastProducts();
        $posts = $this->postService->getRelatedPost($slug);
        $similarCategories = $post->categories;
        $postSlug = $post->slug;
        $questions = resolve(\App\Services\FeServices\CommentService::class)->getQuestionThreadsByPost($post);
        $post = PostDetailDto::from($post);
        $allParentCategories  = \App\Models\Category::where('type', PostType::POST->value)->whereNull('parent_id')->get();
        $logo = asset('storage/' . resolve(SettingFieldService::class)->getKey('logo') );
        SeoHelper::set($post);
        
        return view('web.post.detail', compact('post', 'posts', 'lastProducts', 'similarCategories','allParentCategories', 'logo', 'postSlug', 'questions'));
    }

    public function previewCreate()
    {
        // Kiểm tra xem có dữ liệu preview thêm mới không
        if (!session()->has('post_create_preview')) {
            return redirect()->route('frontend.page.home');
        }

        $previewData = session()->get('post_create_preview');
        
        session()->forget('post_create_preview');

        $post = new Post();
        $post->id = 0;
        $post->title = $previewData['title'] ?? 'Tiêu đề bài viết mới';
        $post->slug = $previewData['slug'] ?? 'chua-co-slug';
        $post->excerpt = $previewData['excerpt'] ?? '';
        $post->content = $previewData['content'] ?? '';
        
        // Gán thời gian mặc định để DTO không bị lỗi type string
        $post->created_at = now();
        $post->updated_at = now();
        
        if (!empty($previewData['image'])) {
            $post->image = $previewData['image'];
        }
        $post->seo_title = $previewData['seo_title'] ?? $post->title;
        $post->seo_keywords = $previewData['seo_keywords'] ?? '';
        $post->seo_description = $previewData['seo_description'] ?? '';
        $post->created_at = now();
        $post->count_view = 0;

        $lastProducts = $this->productService->getLastProducts();
        $posts = collect(); // Bài viết liên quan trống vì chưa có bài viết
        $similarCategories = collect();
        
        $postDto = PostDetailDto::from($post);
        
        $allParentCategories = \App\Models\Category::where('type', PostType::POST->value)->whereNull('parent_id')->get();
        $logo = asset('storage/' . resolve(SettingFieldService::class)->getKey('logo'));
        
        SeoHelper::set([
            'seoTitle' => $previewData['seo_title'] ?? $post->title,
            'seoDescription' => $previewData['seo_description'] ?? $post->excerpt,
            'seoKeyword' => $previewData['seo_keywords'] ?? '',
            'isIndex' => true,
            'canonical' => '',
        ]);

        return view('web.post.detail', [
            'post' => $postDto,
            'posts' => $posts,
            'lastProducts' => $lastProducts,
            'similarCategories' => $similarCategories,
            'allParentCategories' => $allParentCategories,
            'logo' => $logo,
        ]);
    }
}
