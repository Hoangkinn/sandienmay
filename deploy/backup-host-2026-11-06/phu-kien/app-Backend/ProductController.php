<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\FilterFeatured;
use App\Enums\ProductStatus;
use App\Enums\Status;
use App\Enums\Vat;
use App\Exports\ProductsExport;
use App\Helpers\PromotionGift;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $products = Product::select('m_products.*')->orderByDesc('m_products.order')->orderByDesc('m_products.updated_at');
        $products = Product::select('m_products.*')->orderByDesc('m_products.id');

        // if ($request->name) {
        //     $products = $products->where('m_products.name', 'like', '%' . $request->name . '%');
        // }
        if ($request->name) {
            $searchName = trim($request->name);
            if (class_exists('Normalizer')) {
                $searchName = normalizer_normalize($searchName, \Normalizer::FORM_C);
            }
            $words = array_filter(explode(' ', $searchName));

            // Tìm các danh mục khớp với tên tìm kiếm
            $matchingCatIds = Category::where('type', CategoryType::PRODUCT->value)
                ->where(function($q) use ($searchName, $words) {
                    $q->where('name', 'like', '%' . $searchName . '%');
                    if (count($words) > 1) {
                        $q->orWhere(function($subQ) use ($words) {
                            foreach ($words as $w) {
                                if (mb_strlen($w) > 1) {
                                    $subQ->where('name', 'like', '%' . $w . '%');
                                }
                            }
                        });
                    }
                })->pluck('id')->toArray();

            $products = $products->where(function($q) use ($searchName, $words, $matchingCatIds) {
                if (!empty($matchingCatIds)) {
                    $q->whereHas('categories', function($catQ) use ($matchingCatIds) {
                        $catQ->whereIn('m_category_product.category_id', $matchingCatIds);
                    });
                }
                $q->orWhere('m_products.name', 'like', '%' . $searchName . '%');
                if (count($words) > 1) {
                    $q->orWhere(function($subQ) use ($words) {
                        foreach ($words as $w) {
                            if (mb_strlen($w) > 1) {
                                $subQ->where('m_products.name', 'like', '%' . $w . '%');
                            }
                        }
                    });
                }
            });
        }

        if ($request->status) {
            $products = $products->where('m_products.status', $request->status);
        }

        if ($request->featured && $request->featured == FilterFeatured::IS_NEW->value) {
            $products = $products->where('m_products.is_new', true);
        }

        if ($request->featured &&  $request->featured == FilterFeatured::IS_HOT->value) {
            $products = $products->where('m_products.is_hot', true);
        }

        if ($request->featured &&  $request->featured == FilterFeatured::IS_FLASH_SALE->value) {
            $products = $products->where('m_products.is_flash_sale', true);
        }

        if ($request->stock_status) {
            $products = $products->where('m_products.stock_status', $request->stock_status);
        }

        if ($request->category_id) {
            $catInput = (array) $request->category_id;
            $childCatIds = Category::whereIn('parent_id', $catInput)->pluck('id')->toArray();
            $allCategoryIds = array_unique(array_merge($catInput, $childCatIds));
            $catIds = CategoryProduct::select('product_id')->whereIn('category_id', $allCategoryIds)->get();
            $products = $products->whereIn('id', $catIds);
        }
        $products = $products->paginate(30);
        $statusList = Status::cases();
        $productStatusList = ProductStatus::cases();
        $featuredList = FilterFeatured::cases();
        $categories = Category::orderBy('order')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('parent_id', null)
            ->get();

        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'actived')->count();
        $hiddenProducts = Product::where('status', 'inactived')->count();

        return view('backend.product.index', [
            'products' => $products,
            'request' => $request,
            'statusList' => $statusList,
            'featuredList' => $featuredList,
            'categories' => $categories,
            'productStatusList' => $productStatusList,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'hiddenProducts' => $hiddenProducts,
            'oldRequest' => $request->all(),
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
        $vatList = Vat::cases();
        $stockStatusList = ProductStatus::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            // ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();
        $brands = Brand::where('is_active', 1)->get();
        return view('backend.product.create', [
            'categories' => $categories,
            'statusList' => $statusList,
            'vatList' => $vatList,
            'stockStatusList' => $stockStatusList,
            'brands' => $brands
        ]);
    }

    /**
     * Cột promotion lưu HTML nên màn admin nhập theo từng dòng quà tặng phải gộp lại
     * trước khi ghi. Ở chế độ ô soạn thảo cũ (nội dung không phải danh sách quà) thì
     * request không có gift, giữ nguyên giá trị POST.
     */
    private function promotionHtml($request)
    {
        return $request->has('gift')
            ? PromotionGift::toHtml($request->gift)
            : (string) $request->promotion;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // 1. Tạo sản phẩm mới với các thông tin cơ bản trước
            $product = Product::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description, // Tạm thời lưu description gốc
                'content' => $request->content,
                'promotion' => $this->promotionHtml($request),
                'sku' => $request->sku,
                'model' => $request->model,
                'quantity' => $request->quantity,
                'regular_price' => $request->regular_price ? preg_replace('/[^0-9]/', '', $request->regular_price) : 0,
                'sale_price' => $request->sale_price ? preg_replace('/[^0-9]/', '', $request->sale_price) : 0,
                'regular_price_3' => $request->regular_price_3 ? preg_replace('/[^0-9]/', '', $request->regular_price_3) : 0,
                'regular_price_5' => $request->regular_price_5 ? preg_replace('/[^0-9]/', '', $request->regular_price_5) : 0,
                'flash_sale_price' => $request->flash_sale_price,
                'stock_status' => $request->stock_status,
                'status' => $request->status,
                'is_vat' => $request->is_vat,
                'is_new' => $request->is_new,
                'is_hot' => $request->is_hot,
                'is_flash_sale' => $request->is_flash_sale,
                'timeout' => $request->timeout,
                'order' => $request->order,
                'seo_no_index' => $request->seo_no_index,
                'seo_title' => $request->seo_title,
                'seo_keywords' => $request->seo_keywords,
                'seo_description' => $request->seo_description,
                'seo_url_canonical' => $request->seo_url_canonical,
                'schema' => $request->schema,
                'user_id' => Auth::user()->id,
                'youtube_id' => $request->youtube_id,
                'brand_id' => $request->brand_id,
                'warranty' => $request->warranty
            ]);

            if ($product) {
                // 2. Tạo liên kết danh mục sản phẩm
                $categoryIds = $request->category_ids;
                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        CategoryProduct::create([
                            'product_id' => $product->id,
                            'category_id' => $categoryId,
                        ]);
                    }
                }

                // 3. Xử lý phần hình ảnh và mô tả sản phẩm
                if ($request->images) {
                    $images = json_decode($request->images);

                    // Lưu thông tin mảng ảnh giữ nguyên định dạng gốc vào database
                    foreach ($images as $image) {
                        if (empty($image->url)) {
                            continue;
                        }
                        $url = $image->url;
                        $thumbUrl = !empty($image->thumb_url) ? $image->thumb_url : $url;
                        $name = !empty($image->name) ? $image->name : basename($url);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'name' => $name,
                            'url' => $url,
                            'thumb_url' => $thumbUrl,
                            'has_primary' => isset($image->primary) ? $image->primary : false,
                        ]);
                    }
                }
            }

            DB::commit();
            session()->flash('backend.toast.success', 'Thêm mới sản phẩm thành công!');

            if ($request->input('submit') == 'save') {
                return redirect()->route('backend.product.index');
            }

            if ($request->input('submit') == 'save_continue') {
                return redirect()->route('backend.product.edit', ['id' => $product->id]);
            }

            if ($request->input('submit') == 'save_exit') {
                $frontendUrl = route('frontend.slug', ['slug' => $product->slug]);
                return $request->expectsJson()
                    ? response()->json(['redirect_url' => $frontendUrl])
                    : redirect()->to($frontendUrl);
            }

        } catch (\Exception $exception) {
            DB::rollBack();
            logger('[Store Product] error : ' . $exception->getMessage());
            logger($exception->getFile() . ':' . $exception->getLine());
            session()->flash('backend.toast.error', 'Thêm mới sản phẩm không thành công!');
            return redirect()->route('backend.product.create');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        if ($product == null) {
            return redirect()->route('backend.product.index');
        }
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            // ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();
        $productCategories = CategoryProduct::where('product_id', $id)->get();
        $statusList = Status::cases();
        $vatList = Vat::cases();
        $stockStatusList = ProductStatus::cases();
        $images = json_encode($product->images);
        $brands = Brand::where('is_active', 1)->get();

        return view('backend.product.edit', [
            'product' => $product,
            'categories' => $categories,
            'productCategories' => $productCategories,
            'statusList' => $statusList,
            'vatList' => $vatList,
            'stockStatusList' => $stockStatusList,
            'images' => $images,
            'brands' => $brands,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($id);
            if ($product == null) {
                return redirect()->route('backend.product.index');
            }

            $product->name = $request->name;
            $product->slug = $request->slug;
            $product->description = $request->description;

            $product->sku = $request->sku;
            $product->model = $request->model;
            $product->quantity = $request->quantity;
            
            $product->regular_price = $request->regular_price ? preg_replace('/[^0-9]/', '', $request->regular_price) : 0;
            $product->regular_price_3 = $request->regular_price_3 ? preg_replace('/[^0-9]/', '', $request->regular_price_3) : 0;
            $product->regular_price_5 = $request->regular_price_5 ? preg_replace('/[^0-9]/', '', $request->regular_price_5) : 0;
            $product->sale_price = $request->sale_price ? preg_replace('/[^0-9]/', '', $request->sale_price) : 0;
            
            $product->flash_sale_price = $request->flash_sale_price;
            $product->stock_status = $request->stock_status;
            $product->is_vat = $request->is_vat;
            $product->is_new = $request->is_new;
            $product->is_hot = $request->is_hot;
            $product->is_flash_sale = $request->is_flash_sale;
            $product->timeout = $request->timeout;
            $product->order = $request->order;
            $product->content = $request->content;
            $product->promotion = $this->promotionHtml($request);
            $product->status = $request->status;
            $product->seo_no_index = $request->seo_no_index;
            $product->seo_title = $request->seo_title;
            $product->seo_keywords = $request->seo_keywords;
            $product->seo_description = $request->seo_description;
            $product->seo_url_canonical = $request->seo_url_canonical;
            $product->schema = $request->schema;
            $product->youtube_id = $request->youtube_id;
            $product->brand_id = $request->brand_id;
            $product->warranty = $request->warranty;
            $product->save();

            CategoryProduct::where('product_id', $product->id)->delete();
            $categoryIds = $request->category_ids;
            if (!empty($categoryIds)) {
                foreach ($categoryIds as $categoryId) {
                    CategoryProduct::create([
                        'product_id' => $product->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }

            ProductImage::where('product_id', $product->id)->delete();
            
            if ($request->images) {
                $images = json_decode($request->images);

                // Lưu thông tin mảng ảnh giữ nguyên định dạng gốc vào database dữ liệu sản phẩm
                foreach ($images as $image) {
                    if (empty($image->url)) {
                        continue;
                    }
                    $url = $image->url;
                    $thumbUrl = !empty($image->thumb_url) ? $image->thumb_url : $url;
                    $name = !empty($image->name) ? $image->name : basename($url);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'name' => $name,
                        'url' => $url,
                        'thumb_url' => $thumbUrl,
                        'has_primary' => isset($image->primary) ? $image->primary : false,
                    ]);
                }
            }

            DB::commit();
            session()->flash('backend.toast.success', 'Cập nhật sản phẩm thành công!');

            if ($request->input('submit') == 'save') {
                return redirect()->route('backend.product.index');
            }

            if ($request->input('submit') == 'save_continue') {
                return redirect()->route('backend.product.edit', ['id' => $product->id]);
            }

            if ($request->input('submit') == 'save_exit') {
                $frontendUrl = route('frontend.slug', ['slug' => $product->slug]);
                return $request->expectsJson()
                    ? response()->json(['redirect_url' => $frontendUrl])
                    : redirect()->to($frontendUrl);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            logger('update product error : ' . $exception->getMessage());
            logger($exception->getFile() . ':' . $exception->getLine());
            session()->flash('backend.toast.error', 'Cập nhật sản phẩm không thành công!');
            return redirect()->route('backend.product.edit', ['id' => $id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Kiểm tra xem sản phẩm có nằm trong đơn hàng nào không
        $hasOrders = DB::table('m_order_items')->where('product_id', $id)->exists();
        if ($hasOrders) {
            session()->flash('backend.toast.error', 'Sản phẩm đã phát sinh đơn hàng, không thể xóa!');
            return redirect()->back();
        }

        CategoryProduct::where('product_id', $id)->delete();
        ProductImage::where('product_id', $id)->delete();
        DB::table('m_product_attributes')->where('product_id', $id)->delete();

        $product = Product::find($id);
        if ($product == null) {
            return redirect()->route('backend.product.index');
        }

        $product->delete();

        session()->flash('backend.toast.success', 'Xóa sản phẩm thành công!');
        return redirect()->route('backend.product.index');
    }
    // public function destroy($id)
    // {
    //     CategoryProduct::where('product_id', $id)->delete();
    //     ProductImage::where('product_id', $id)->delete();
    //     $product = Product::find($id);
    //     if ($product == null) {
    //         return redirect()->route('backend.product.index');
    //     }

    //     $product->delete();

    //     session()->flash('backend.toast.success', 'Xóa sản phẩm thành công!');
    //     return redirect()->route('backend.product.index');
    // }

    public function attribute($id)
{
    $product = Product::with('categories')->find($id);
    if ($product == null) {
        return redirect()->route('backend.product.index');
    }
    $categoryIds = $product->categories->pluck('id')->toArray();

    $attributes = Attribute::query()
    ->leftJoin('m_product_attributes', function ($join) use ($id) {
        $join->on('m_attributes.id', '=', 'm_product_attributes.attribute_id')
             ->where('m_product_attributes.product_id', '=', $id);
    })
    ->where('m_attributes.status', Status::ACTIVED->value)
    ->where(function ($q) use ($categoryIds) {
        $q->whereHas('categories', function ($q2) use ($categoryIds) {
            $q2->whereIn('m_categories.id', $categoryIds);
        })
        ->orWhereDoesntHave('categories');
    })
    // Chỉ đẩy NULL xuống dưới (trả về 1), còn số 0 và các số > 0 sẽ được sắp xếp tăng dần bình thường (0 đứng đầu)
    ->orderByRaw('CASE WHEN m_product_attributes.position IS NULL THEN 1 ELSE 0 END, m_product_attributes.position ASC')
    ->select('m_attributes.*')
    ->get();

    // Lấy danh sách option đã chọn kèm theo tên đầy đủ dạng [attribute_id => [ [id, name], ... ]]
    $selectedOptions = [];
    $productAttrs = ProductAttribute::where('product_id', $id)->get();
    
    if ($productAttrs->isNotEmpty()) {
        $optionIds = $productAttrs->pluck('option_id')->toArray();
        $optionsMap = \App\Models\AttributeOption::whereIn('id', $optionIds)->get()->keyBy('id');

        foreach ($productAttrs as $pa) {
            if (isset($optionsMap[$pa->option_id])) {
                $selectedOptions[$pa->attribute_id][] = [
                    'id' => $pa->option_id,
                    'name' => $optionsMap[$pa->option_id]->name
                ];
            }
        }
    }

    $productAttributes = ProductAttribute::where('product_id', $id)
        ->select('attribute_id', 'position')
        ->groupBy('attribute_id', 'position')
        ->pluck('position', 'attribute_id')
        ->toArray();

    return view('backend.product.attribute', [
        'product' => $product,
        'attributes' => $attributes,
        'productAttributes' => $productAttributes,
        'selectedOptions' => $selectedOptions,
    ]);
}

// Thêm phương thức phục vụ Ajax Select2
public function getAttributeOptions(Request $request, $attributeId)
{
    $keyword = $request->get('keyword', '');

    $options = \App\Models\AttributeOption::where('attribute_id', $attributeId)
        ->where('status', \App\Enums\Status::ACTIVED->value)
        ->when($keyword, function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%');
        })
        ->limit(20)
        ->get(['id', 'name']);

    return response()->json($options);
}

    public function saveProductAttribute(Request $request, $product_id)
    {
        DB::beginTransaction();
        try {
            ProductAttribute::where('product_id', $product_id)->delete();

            $attributes = $request->input('attribute_options', []);
            $positions  = $request->input('positions', []);

            $items = [];

            foreach ($attributes as $attribute_id => $options) {
                $position = $positions[$attribute_id] ?? 0;
                foreach ($options as $option) {

                    if (is_numeric($option)) {
                        $optionId = (int) $option;
                    }
                    else {
                        $optionModel = AttributeOption::firstOrCreate(
                            [
                                'attribute_id' => $attribute_id,
                                'name' => trim($option),
                            ],
                            [
                                'status'   => Status::ACTIVED->value,
                                'position' => 0,
                                'user_id'  => Auth::id()
                            ]
                        );

                        $optionId = $optionModel->id;
                    }

                    $items[] = [
                        'product_id'   => $product_id,
                        'attribute_id' => $attribute_id,
                        'option_id'    => $optionId,
                        'position'     => $position,
                    ];
                }
            }

            if (!empty($items)) {
                ProductAttribute::insert($items);
            }

            DB::commit();

            session()->flash('backend.toast.success', 'Lưu thuộc tính sản phẩm thành công!');

            return $request->input('submit') === 'save'
                ? redirect()->route('backend.product.edit', $product_id)
                : redirect()->route('backend.product.attribute', $product_id);
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Save product attribute failed', [
                'product_id' => $product_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash(
                'backend.toast.error',
                $e->getMessage()
            );

            return back()->withInput();
        }
    }


    public function updateFeature(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        if ($product == null) {
            return redirect()->route('backend.product.index');
        }

        $product->is_new = $request->is_new;
        $product->is_hot = $request->is_hot;
        $product->is_flash_sale = $request->is_flash_sale;
        $product->save();

        session()->flash('backend.toast.success', 'Cập nhật sản phẩm thành công!');
        return redirect()->route('backend.product.index');
    }

    public function exportProducts()
    {
        return Excel::download(
            new ProductsExport(),
            'products_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function deleteMultiple(Request $request)
{
    // Lấy ids có thể dưới dạng mảng hoặc chuỗi phân cách dấu phẩy
    $ids = $request->input('ids');

    if (is_string($ids)) {
        $ids = explode(',', $ids);
    }

    if (empty($ids) || !is_array($ids)) {
        return redirect()->back()->with('backend.toast.error', 'Vui lòng chọn ít nhất một sản phẩm để xóa!');
    }

    try {
        \DB::beginTransaction();

        foreach ($ids as $id) {
            $id = trim($id);
            if (empty($id)) continue;

            // 1. Kiểm tra đơn hàng
            $hasOrders = DB::table('m_order_items')->where('product_id', $id)->exists();
            if ($hasOrders) {
                continue; // Bỏ qua sản phẩm dính đơn hàng
            }

            $product = \App\Models\Product::find($id);
            if (!$product) {
                continue;
            }

            // 2. Xóa bảng liên quan
            CategoryProduct::where('product_id', $id)->delete();
            ProductImage::where('product_id', $id)->delete();
            DB::table('m_product_attributes')->where('product_id', $id)->delete();

            // 3. Xóa ảnh
            if ($product->image && \Storage::exists($product->image)) {
                \Storage::delete($product->image);
            }

            // 4. Detach quan hệ
            if (method_exists($product, 'categories')) {
                $product->categories()->detach();
            }

            // 5. Xóa sản phẩm
            $product->delete();
        }

        \DB::commit();
        return redirect()->back()->with('backend.toast.success', 'Xóa sản phẩm thành công!');
    } catch (\Exception $e) {
        \DB::rollBack();
        // Trả về lỗi chi tiết để biết văng ra ở đâu thay vì nuốt lỗi
        return redirect()->back()->with('backend.toast.error', 'Lỗi: ' . $e->getMessage());
    }
}
}
