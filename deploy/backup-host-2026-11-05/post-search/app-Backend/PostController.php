<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\PostType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $posts = Post::orderByDesc('order')->orderByDesc('created_at')
            ->where('type', PostType::POST->value);

        // 1. Tách riêng tìm theo Tiêu đề chính
        if ($request->filled('title')) {
            $posts = $posts->where('title', 'like', '%' . $request->title . '%');
        }

        // 2. Tìm theo Nội dung
        if ($request->filled('excerpt')) {
            $posts = $posts->where('excerpt', 'like', '%' . $request->excerpt . '%');
        }

        // 3. Tìm theo các trường SEO (Tiêu đề SEO, Từ khóa SEO, Mô tả SEO)
        if ($request->filled('seo_keyword')) {
            $seoKey = $request->seo_keyword;
            $posts = $posts->where(function ($query) use ($seoKey) {
                $query->where('seo_title', 'like', '%' . $seoKey . '%')
                    ->orWhere('seo_keywords', 'like', '%' . $seoKey . '%')
                    ->orWhere('seo_description', 'like', '%' . $seoKey . '%');
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $posts = $posts->where('status', $request->status);
        }

        $totalPosts = Post::where('type', PostType::POST->value)->count();
        $activePosts = Post::where('type', PostType::POST->value)->where('status', 'actived')->count();
        $hiddenPosts = Post::where('type', PostType::POST->value)->where('status', 'inactived')->count();

        $posts = $posts->paginate(20);
        $statusList = Status::cases();

        return view('backend.post.index', [
            'posts' => $posts,
            'request' => $request,
            'statusList' => $statusList,
            'totalPosts' => $totalPosts,
            'activePosts' => $activePosts,
            'hiddenPosts' => $hiddenPosts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::POST->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();

        return view('backend.post.create', [
            'categories' => $categories,
            'statusList' => $statusList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $fileName = '';
        if ($request->image) {
            $file = $request->image;

            // Giữ nguyên định dạng ảnh gốc, chỉ thêm prefix thời gian cho khỏi trùng
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = time() . '_' . $originalName . '.' . $file->guessExtension();

            $uploadPath = public_path('storage');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $fileName);
        }

        $content = $request->content;

        $post = Post::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'excerpt' => $request->excerpt,
            'image' => $fileName,
            'content' => $content, 
            'status' => $request->status,
            'type' => PostType::POST->value,
            'order' => $request->order,
            'seo_no_index' => $request->seo_no_index,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'seo_url_canonical' => $request->seo_url_canonical,
            'schema' => $request->schema,
            'user_id' => Auth::user()->id,
        ]);

        if ($post) {
            $categoryIds = $request->category_ids;
            if ($categoryIds && is_array($categoryIds)) {
                foreach ($categoryIds as $categoryId) {
                    CategoryPost::create([
                        'post_id' => $post->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }
        }

        session()->flash('backend.toast.success', 'Lưu bài viết thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.post.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.post.edit', ['id' => $post->id]);
        }

        if ($request->input('submit') == 'save_exit') {
            $frontendUrl = route('frontend.slug', ['slug' => $post->slug]);
            return $request->expectsJson()
                ? response()->json(['redirect_url' => $frontendUrl])
                : redirect()->to($frontendUrl);
        }

        if ($request->input('submit') == 'preview') {
            return redirect()->route('frontend.slug', ['slug' => $post->slug]);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $statusList = Status::cases();
        $post = Post::find($id);
        if ($post == null) {
            return redirect()->route('backend.post.index');
        }
        session()->forget('post_preview_' . $id);
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::POST->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();
        $categoryMapping = CategoryPost::where('post_id', $id)->first();


        return view('backend.post.edit', [
            'post' => $post,
            'categories' => $categories,
            'categoryId' => $categoryMapping ? $categoryMapping->category_id : '',
            'statusList' => $statusList
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePostRequest  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::find($id);
        if ($post == null) {
            return redirect()->route('backend.post.index');
        }
        session()->forget('post_preview_' . $id);

        // 1. NẾU LÀ NÚT XEM TRƯỚC: Lưu tạm vào Session và chuyển hướng, TUYỆT ĐỐI KHÔNG SAVE DB
        if ($request->input('submit') == 'preview') {
            session()->put('post_preview_' . $id, $request->all());
            $slug = $request->slug ?? $post->slug;
            return redirect()->to(route('frontend.slug', ['slug' => $slug]));
        }

        // 2. NẾU LÀ NÚT LƯU THẬT: Xóa session preview cũ (nếu có) rồi tiến hành ghi đè Database
        session()->forget('post_preview_' . $id);

        $post->title = $request->title;
        $post->slug = $request->slug;
        $post->excerpt = $request->excerpt;

        if ($request->image) {
            $file = $request->image;

            // Giữ nguyên định dạng ảnh gốc cho Ảnh Đại Diện
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = time() . '_' . $originalName . '.' . $file->guessExtension();

            $uploadPath = public_path('storage');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $fileName);

            $post->image = $fileName;
        }

        // Giữ nguyên hoàn toàn nội dung từ editor gửi lên để không bao giờ bị mất ảnh cũ.
        $content = $request->content;

        $post->content = $content; 
        $post->status = $request->status;
        $post->order = $request->order;
        $post->seo_no_index = $request->seo_no_index;
        $post->seo_title = $request->seo_title;
        $post->seo_keywords = $request->seo_keywords;
        $post->seo_description = $request->seo_description;
        $post->seo_url_canonical = $request->seo_url_canonical;
        $post->schema = $request->schema;
        $post->save();

        CategoryPost::where('post_id', $post->id)->delete();
        $categoryIds = $request->category_ids;
        if ($categoryIds && is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                CategoryPost::create([
                    'post_id' => $post->id,
                    'category_id' => $categoryId,
                ]);
            }
        }

        session()->flash('backend.toast.success', 'Lưu bài viết thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.post.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.post.edit', ['id' => $post->id]);
        }

        if ($request->input('submit') == 'save_exit') {
            $frontendUrl = route('frontend.slug', ['slug' => $post->slug]);
            return $request->expectsJson()
                ? response()->json(['redirect_url' => $frontendUrl])
                : redirect()->to($frontendUrl);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        CategoryPost::where('post_id', $id)->delete();
        $post = Post::find($id);
        $post->delete();

        session()->flash('backend.toast.success', 'Xóa bài viết thành công!');
        return redirect()->route('backend.post.index');
    }

    public function updateFeatured(Request $request, $id)
    {
        $post = \App\Models\Post::findOrFail($id);
        $post->is_featured = $request->is_featured;
        $post->save();

        return response()->json(['messages' => 'Cập nhật thành công!']);
    }

    public function preview(Request $request, $id)
    {
        $post = Post::find($id);
        if ($post == null) {
            return redirect()->route('backend.post.index');
        }

        session()->put('post_preview_' . $id, $request->all());

        $slug = $request->slug ?? $post->slug;
        return redirect()->to(route('frontend.slug', ['slug' => $slug]));
    }

    public function previewCreate(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp,gif|max:100',
        ], [
            'image.max' => 'Ảnh đại diện bài viết không được vượt quá 100KB. Vui lòng giảm dung lượng ảnh rồi tải lại.',
            'image.image' => 'Ảnh đại diện phải là một hình ảnh.',
        ]);

        $data = $request->except(['image']);

        // Nếu có chọn ảnh mới khi thêm
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Lưu tạm file vào thư mục posts/preview trong storage
            $path = $file->store('posts/preview', 'public');
            // Lưu đường dẫn vào mảng dữ liệu session
            $data['image'] = $path; 
        }

        session()->put('post_create_preview', $data);

        return redirect()->route('frontend.post.preview.create');
    }

    /*** Xóa nhiều bài viết cùng lúc*/
    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('backend.toast.error', 'Vui lòng chọn ít nhất một bài viết để xóa!');
        }

        try {
            $posts = \App\Models\Post::whereIn('id', $ids)->get();

            foreach ($posts as $post) {
                // Xóa ảnh trong storage
                if ($post->image && \Storage::exists($post->image)) {
                    \Storage::delete($post->image);
                }

                // Gỡ liên kết bảng trung gian tránh lỗi khóa ngoại
                if (method_exists($post, 'categories')) {
                    $post->categories()->detach();
                }

                // Xóa bài viết
                $post->delete();
            }

            // Trả về thông báo thành công dạng Toast như bạn muốn
            return redirect()->back()->with('backend.toast.success', 'Xóa bài viết thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('backend.toast.error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    
}