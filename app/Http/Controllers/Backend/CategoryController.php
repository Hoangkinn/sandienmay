<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Helpers\UploadImageHelper;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $type)
{
    $query = Category::orderBy('order')
        ->where('type', $type)
        ->where('parent_id', null);

    // Lọc theo trạng thái nếu người dùng bấm nút (Tổng, Hiển thị, Ẩn)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $categories = $query->get();

    $totalCategories = Category::where('type', $type)->count();
    $activeCategories = Category::where('type', $type)->where('status', 'actived')->count(); 
    $hiddenCategories = Category::where('type', $type)->where('status', 'inactived')->count();

    return view('backend.category.index2', [
        'categories' => $categories,
        'type' => $type,
        'totalCategories' => $totalCategories,
        'activeCategories' => $activeCategories,
        'hiddenCategories' => $hiddenCategories,
    ]);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type)
    {
        $statusList = Status::cases();
        $statusSimilarProduct = Status::cases();
        $categories = Category::orderBy('order')
            ->where('type', $type)
            ->where('parent_id', null)
            ->where('status', Status::ACTIVED->value)
            ->get();

        $brands = \App\Models\Brand::all();

        return view('backend.category.create', [
            'categories' => $categories,
            'brands' => $brands,
            'type' => $type,
            'statusList' => $statusList,
            'statusSimilarProduct' => $statusSimilarProduct
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request, $type)
    {
        $fileName = '';
        if ($request->image) {
            $fileName = UploadImageHelper::store($request->image, 'storage', false)['name'];
        }
        
        $thumbData = []; 
        // Giả sử bạn có thêm 1 input phụ nữa tên là 'icon_extra' trong form
        if ($request->icon) {
            $thumbData['main'] = UploadImageHelper::store($request->icon, 'storage', false)['name']; // Ảnh thumb chính
        }

        if ($request->hasFile('icon_extra')) { // Ảnh phụ chọn cùng lúc
            $extraName = 'extra-' . time() . '.' . $request->icon_extra->getClientOriginalExtension();
            $request->icon_extra->move(public_path('storage'), $extraName);
            $thumbData['extra'] = $extraName; // Ảnh phụ
        }
        // $fileNameIcon = '';
        // if ($request->icon) {
        //     $iconWithoutExtension = pathinfo($request->icon->getClientOriginalName(), PATHINFO_FILENAME);
        //     $iconExtension = $request->icon->getClientOriginalExtension();
            
        //     $fileNameIcon = $iconWithoutExtension . '-' . time() . '.' . $iconExtension;
        //     $request->icon->move(public_path('storage'), $fileNameIcon);
        // }

        $category = Category::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'excerpt' => $request->excerpt,
            'image' => $fileName,
            // 'icon' => $fileNameIcon,
            'icon' => !empty($thumbData) ? json_encode($thumbData) : '',
            'content' => $request->content,
            'status' => $request->status,
            'type' => $type,
            'is_home_page' => $request->is_home_page,
            'badge' => $request->input('badge') ?: null,
            'parent_id' => $request->parent_id,
            'display_type' => $request->input('display_type', 'attribute'),
            'seo_no_index' => $request->seo_no_index,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'seo_url_canonical' => $request->seo_url_canonical,
            'schema' => $request->schema,
            'user_id' => Auth::user()->id,
            'status_similar_product' => $request->status_similar_product,
            'coupon_data' => $request->input('coupon', null),
        ]);

        if ($request->input('display_type') === 'brand' && $request->filled('brand_id')) {
            $category->brands()->sync([$request->brand_id]);
        }
        
        Cache::forget('product_categories_parent_v1');

        session()->flash('backend.toast.success', 'Thêm mới danh mục thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.category.index', ['type' => $type]);
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.category.edit', ['id' => $category->id, 'type' => $type]);
        }

        if ($request->input('submit') == 'save_exit') {
            $frontendUrl = route('frontend.slug', ['slug' => $category->slug]);
            return $request->expectsJson()
                ? response()->json(['redirect_url' => $frontendUrl])
                : redirect()->to($frontendUrl);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($type, $id)
    {
        $statusList = Status::cases();
        $statusSimilarProduct = Status::cases();
        $category = Category::where('id', $id)
            ->where('type', $type)
            ->first();

        if ($category == null) {
            return redirect()->route('backend.category.index', [
                'type' => $type
            ]);
        }

        $categoyParents = Category::orderBy('order')
            ->where('type', $type)
            ->where('parent_id', null)
            ->where('status', Status::ACTIVED->value)
            ->get();

        $brands = \App\Models\Brand::all();

        // Khối "Mức giá" + "Thông số" chỉ hiện với danh mục cấp 1, loại sản phẩm.
        $isRootProductCate = empty($category->parent_id)
            && $type == \App\Enums\CategoryType::PRODUCT->value;

        return view('backend.category.edit', [
            'category' => $category,
            'categoyParents' => $categoyParents,
            'type' => $type,
            'statusList' => $statusList,
            'statusSimilarProduct' => $statusSimilarProduct,
            'brands' => $brands,
            'isRootProductCate' => $isRootProductCate,
            'priceBands' => $isRootProductCate ? $category->priceBands : collect(),
            'specOptionIds' => $isRootProductCate ? $category->specs->pluck('option_id')->all() : [],
            'attributeGroups' => $isRootProductCate
                ? \App\Models\Attribute::with(['options' => fn($q) => $q->where('status', \App\Enums\Status::ACTIVED->value)->orderBy('position')->orderBy('id')])
                    ->where('status', Status::ACTIVED->value)
                    ->orderBy('position')->orderBy('name')->get()
                : collect(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, $type, $id)
    {
        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->excerpt = $request->excerpt;

        if ($request->image) {
            $category->image = UploadImageHelper::store($request->image, 'storage', false)['name'];
        }

        $thumbData = json_decode($category->icon, true) ?? [];
        if ($request->hasFile('icon')) {
            $thumbData['main'] = UploadImageHelper::store($request->icon, 'storage', false)['name']; // Cập nhật ảnh chính
        }

        // 3. Nếu có upload icon phụ mới (extra)
        if ($request->hasFile('icon_extra')) {
            $extraName = 'extra-' . time() . '.' . $request->icon_extra->getClientOriginalExtension();
            $request->icon_extra->move(public_path('storage'), $extraName);
            $thumbData['extra'] = $extraName; // Cập nhật ảnh phụ
        }

        // 4. Lưu lại vào DB dưới dạng JSON
        $category->icon = json_encode($thumbData);
        // if ($request->icon) {
        //     $iconWithoutExtension = pathinfo($request->icon->getClientOriginalName(), PATHINFO_FILENAME);
        //     $iconExtension = $request->icon->getClientOriginalExtension();
            
        //     $fileNameIcon = $iconWithoutExtension . '-' . time() . '.' . $iconExtension;
        //     $request->icon->move(public_path('storage'), $fileNameIcon);
        //     $category->icon = $fileNameIcon;
        // }

        $category->status = $request->status;
        $category->status_similar_product = $request->status_similar_product;
        $category->parent_id = $request->parent_id;
        $category->is_home_page = $request->is_home_page;
        $category->badge = $request->input('badge') ?: null;
        $category->seo_no_index = $request->seo_no_index;
        $category->seo_title = $request->seo_title;
        $category->seo_keywords = $request->seo_keywords;
        $category->seo_description = $request->seo_description;
        $category->seo_url_canonical = $request->seo_url_canonical;
        $category->schema = $request->schema;
        $category->coupon_data = $request->input('coupon', null);
        $category->display_type = $request->input('display_type', 'attribute');
        $category->save();

        // Mức giá + thông số chỉ cấu hình ở danh mục cấp 1 (danh mục con đã là một trang lọc riêng).
        // Form hiện không còn gửi 2 khối này; chỉ đồng bộ khi request thật sự có dữ liệu để
        // tránh việc lưu danh mục làm xoá mất cấu hình cũ.
        if (empty($category->parent_id)) {
            if ($request->has('price_band')) {
                \App\Helpers\CategoryFilterConfig::syncPriceBands(
                    (int) $category->id,
                    (array) $request->input('price_band', [])
                );
            }
            if ($request->has('spec_option')) {
                \App\Helpers\CategoryFilterConfig::syncSpecs(
                    (int) $category->id,
                    (array) $request->input('spec_option', [])
                );
            }
        }

        // Menu và sidebar lọc trang danh mục đọc 2 bảng này, sửa xong phải tính lại ngay.
        Cache::forget('fe_menu_filters_v1');

        // Xử lý lưu quan hệ Brand nếu chọn kiểu là brand
        if ($request->display_type === 'brand' && $request->filled('brand_id')) {
            // Truyền vào mảng chứa đúng 1 ID được chọn
            $category->brands()->sync([$request->brand_id]);
        } else {
            // Nếu không chọn hoặc chọn tính năng thì gỡ bỏ liên kết brand
            $category->brands()->detach();
        }

        Cache::forget('product_categories_parent_v1');

        session()->flash('backend.toast.success', 'Cập nhật danh mục thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.category.index', ['type' => $type]);
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.category.edit', ['id' => $category->id, 'type' => $type]);
        }

        if ($request->input('submit') == 'save_exit') {
            $frontendUrl = route('frontend.slug', ['slug' => $category->slug]);
            return $request->expectsJson()
                ? response()->json(['redirect_url' => $frontendUrl])
                : redirect()->to($frontendUrl);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($type, $id)
    {
        $category = Category::find($id);
        if ($category == null) {
            return redirect()->route('backend.category.index', ['type' => $type]);
        }

        // Xóa các liên kết sản phẩm thuộc danh mục này trong bảng trung gian
        DB::table('m_category_product')->where('category_id', $id)->delete();

        // (Tuỳ chọn) Nếu danh mục của bạn có phân cấp cha/con và cần xóa hoặc cập nhật danh mục con:
        // DB::table('m_categories')->where('parent_id', $id)->update(['parent_id' => 0]);

        $category->delete();

        session()->flash('backend.toast.success', 'Xóa danh mục thành công!');
        return redirect()->route('backend.category.index', ['type' => $type]);
    }
    // public function destroy($type, $id)
    // {
    //     $category = Category::find($id);
    //     $category->delete();

    //     session()->flash('backend.toast.success', 'Xóa danh mục thành công!');
    //     return redirect()->route('backend.category.index', ['type' => $type]);
    // }

    /**
     * Update sort the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function updateSort(Request $request, $type)
    {
        $menus = json_decode($request->json);
        if ($menus) {
            foreach ($menus as $order => $menu) {
                Category::where('type', $type)
                    ->where('id', $menu->id)
                    ->update([
                        'order' => $order + 1,
                        'parent_id' => null,
                    ]);

                if ($menu->children) {
                    self::updateChild($menu->children, $menu->id, $type);
                }
            }

            return [
                'message' => 'Lưu thứ tự sắp xếp thành công.'
            ];
        }
    }

    protected function updateChild($menus, $parent_id, $type)
    {
        foreach ($menus as $order => $menu) {
            Category::where('type', $type)
                ->where('id', $menu->id)
                ->update([
                    'order' => ($order + 1),
                    'parent_id' => $parent_id,
                ]);

            if ($menu->children) {
                self::updateChild($menu->children, $menu->id, $type);
            }
        }
    }

    public function toggleMenu(Request $request)
    {
        $category = Category::find($request->id);
        if ($category) {
            $category->is_menu = $request->is_menu;
            $category->save();
            
            // Xóa cache menu nếu bạn có dùng cache
            Cache::forget('product_categories_parent_v1'); 
            
            return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái menu']);
        }
        return response()->json(['success' => false, 'message' => 'Danh mục không tồn tại']);
    }

    public function updateBadge(Request $request)
    {
        $category = Category::find($request->id);
        if ($category) {
            $badge = $request->input('badge');
            $category->badge = in_array($badge, ['hot', 'new'], true) ? $badge : null;
            $category->save();

            Cache::forget('product_categories_parent_v1');
            Cache::forget('product_categories_parent_v3');
            Cache::forget('product_categories_all');

            return response()->json(['success' => true, 'message' => 'Đã cập nhật nhãn danh mục']);
        }
        return response()->json(['success' => false, 'message' => 'Danh mục không tồn tại']);
    }

    public function preview(Request $request, $type)
    {
        if ($type !== \App\Enums\CategoryType::PRODUCT->value) {
            return redirect()->back()->with('error', 'Chức năng xem trước chỉ áp dụng cho danh mục sản phẩm.');
        }

        // 1. Khởi tạo model Category tạm thời và fill toàn bộ dữ liệu từ request lên
        $category = new Category($request->all());
        $category->type = $type;
        $category->id = 0;

        // Gán đồng thời vào tất cả các tên cột phổ biến để chắc chắn view gọi trúng biến nào cũng hiển thị
        // $contentData = $request->input('excerpt') ?? $request->input('content') ?? $request->input('description') ?? '';
        // Sửa lại cách lấy dữ liệu cho từng cột thay vì gộp chung ghi đè
    $category->excerpt     = $request->input('excerpt', '');
    $category->description = $request->input('description', '');
    $category->content     = $request->input('content', '');
    $category->body        = $request->input('body', '');

    // Chọn đúng trường chứa nội dung HTML của trình soạn thảo để làm Mục lục (TOC)
    // Thường trình soạn thảo sẽ lưu vào 'description' hoặc 'content'
    $contentData = $request->input('description') ?? $request->input('content') ?? $request->input('excerpt') ?? '';
        $category->excerpt = $contentData;
        $category->content = $contentData;
        $category->description = $contentData;
        $category->body = $contentData;; 

        // Xử lý tạm thời ảnh preview (nếu có tải lên ảnh mới)
        if ($request->hasFile('image')) {
            $fileName = 'preview-' . time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('storage'), $fileName);
            $category->image = $fileName;
        }

        // 2. Khai báo các biến giả lập cho view Frontend
        $parentCategory = null;
        $productsPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 16);
        $products = \App\DTOs\Product\ProductListDto::from($productsPaginator);
        $relatedCat = [];
        $filterPriceEnum = null;
        $sortEnum = null;
        $paginate = '';
        $brands = collect();
        $brand_id = null;
        $total = 0;
        $price = null;
        
        $cateBr = [
            ['name' => $category->name ?? 'Xem trước danh mục', 'url' => '#']
        ];
        
        $news = collect();
        $priceSorts = [];
        $otherSorts = [];
        $viewedProducts = collect();
        $viewedPaginate = null;
        $toc = [];

        // Cài đặt SEO tạm thời
        if (class_exists('\App\Helpers\SeoHelper')) {
            \App\Helpers\SeoHelper::setTitle($category->seo_title ?: ($category->name ?? 'Xem trước danh mục'));
            \App\Helpers\SeoHelper::setDescription($category->seo_description ?? '');
            \App\Helpers\SeoHelper::setUrl($category->seo_url_canonical ?? url()->current());
            \App\Helpers\SeoHelper::setRobots($category->robots ?? 'index,follow');
        }

        $toc = [];
        $contentForToc = $category->excerpt ?? '';
        
        if (!empty($contentForToc)) {
            // Dùng preg_replace_callback để vừa bắt h2/h3 vừa gắn id, đồng thời decode ký tự HTML để hiển thị chuẩn tiếng Việt
            $currentH2Index = -1;
            
            $contentForToc = preg_replace_callback('/<(h[23])([^>]*)>(.*?)<\/\1>/ui', function ($matches) use (&$toc, &$currentH2Index) {
                $tag = strtolower($matches[1]);
                $attr = $matches[2];
                $innerHtml = $matches[3];
                
                // Lấy text sạch và giải mã HTML entities để giữ nguyên tiếng Việt có dấu
                $text = html_entity_decode(strip_tags($innerHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $id = '';
                if (preg_match('/id=["\']([^"\']+)["\']/ui', $attr, $idMatch)) {
                    $id = $idMatch[1];
                } else {
                    if ($tag === 'h2') {
                        $id = 'heading-h2-' . (count($toc) + 1);
                    } else {
                        $id = 'heading-h3-' . ($currentH2Index >= 0 ? count($toc[$currentH2Index]['children']) + 1 : 1);
                    }
                    $attr .= ' id="' . $id . '"';
                }

                if ($tag === 'h2') {
                    $toc[] = [
                        'id' => $id,
                        'title' => $text,
                        'children' => []
                    ];
                    $currentH2Index = count($toc) - 1;
                } else {
                    if ($currentH2Index === -1) {
                        $toc[] = [
                            'id' => 'heading-h2-1',
                            'title' => 'Mục lục chung',
                            'children' => []
                        ];
                        $currentH2Index = 0;
                    }
                    $toc[$currentH2Index]['children'][] = [
                        'id' => $id,
                        'title' => $text
                    ];
                }

                return '<' . $tag . $attr . '>' . $innerHtml . '</' . $tag . '>';
            }, $contentForToc);

            // Gán lại nội dung đã chèn ID
            $category->excerpt = $contentForToc;
            $category->content = $contentForToc;
        }

        // Đẩy mảng $toc vào biến category để truyền ra view
        if (is_array($category)) {
            $category['toc'] = $toc;
        } else {
            $category->toc = $toc;
        }

        // 3. Trả về view kèm đầy đủ bộ biến
        return view('web/product/category', compact(
            'parentCategory', 'category', 'products', 'relatedCat', 
            'filterPriceEnum', 'sortEnum', 'paginate', 'brands', 'brand_id', 
            'total', 'price', 'cateBr', 'news', 'priceSorts', 'otherSorts', 
            'viewedProducts', 'viewedPaginate', 'toc'
        ));
    }

    public function previewEdit(Request $request, $type, $id)
    {
        // 1. Kiểm tra xem danh mục có tồn tại không để tránh lỗi văng null
        $category = Category::find($id);
        
        if (!$category) {
            return redirect()->back()->with('error', 'Không tìm thấy danh mục để xem trước.');
        }

        $previewId = uniqid(); 
        
        // 2. Chuẩn bị dữ liệu lưu session (có thể kết hợp giữa dữ liệu cũ trong DB và dữ liệu mới sửa trên form)
        $previewData = $request->all();
        
        // Đảm bảo có luôn ID và Slug để bên frontend không bị hụt
        $previewData['id'] = $category->id;
        $previewData['slug'] = $previewData['slug'] ?? $category->slug;

        // Lưu dữ liệu vào session theo cả previewId lẫn ID bản ghi
        session()->put('category_preview_' . $previewId, $previewData);
        session()->put('category_preview_' . $id, $previewData); 
        session()->put('category_preview_current', $previewData); // Key dự phòng chung

        // 3. Tạo URL frontend chính xác từ slug của danh mục
        $frontendUrl = route('frontend.slug', ['slug' => $previewData['slug']]) . '?preview_id=' . $previewId;

        return redirect()->to($frontendUrl);
    }
}
