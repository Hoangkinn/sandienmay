
<x-app-layout>
<style>
    .breadcrumb-custom a {
        color: #D80000;
        border: 1px solid #d80000;
        padding: 3px 7px 4px;
        font-size: 15px;
        margin-right: 10px;
        transition: all 0.3s ease
    } 

    .breadcrumb-custom a:hover {
        background: #d80000;
        color: #fff;
    }
    .tox .tox-toolbar--scrolling {
        flex-wrap: wrap !important;
    }
    
</style>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ ($type == App\Enums\CategoryType::PRODUCT->value) ? __('Cập nhật danh mục sản phẩm') : __('Cập nhật danh mục tin tức') }}
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-custom"><a href="{{ route('frontend.slug', ['slug' => $category->slug]) }}" target="_blank" rel="noopener noreferrer">Xem trên web</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.category.index', ['type' => $type]) }}">{{ ($type == App\Enums\CategoryType::PRODUCT->value) ? __('Danh mục sản phẩm') : __('Danh mục tin tức') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('Cập nhật') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.category.update', ['type' => $type, 'id' => $category->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin danh mục') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">{{ __('Tên danh mục') }} <code>*</code></label>
                                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                        class="form-control @error('name') is-invalid @enderror" id="name"
                                        placeholder="Tên danh mục" autofocus>
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                                        class="form-control @error('slug') is-invalid @enderror" placeholder="Đường dẫn">
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Danh mục cha') }}</label>
                                    <select name="parent_id" class="form-control select2">
                                        <option value="">None</option>
                                        @foreach ($categoyParents as $categoyParent)
                                        <option value="{{ $categoyParent->id }}" @selected($category->parent_id ==
                                            $categoyParent->id)>
                                            {{ $categoyParent->name }}
                                        </option>
                                        @if(count($categoyParent->childs))
                                        @include('backend.category.child.edit', [
                                        'childs' => $categoyParent->childs,
                                        'expandable' => '--',
                                        'categoryId' => $category->parent_id
                                        ])
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="is_home_page"
                                            type="checkbox" id="is_home_page" @checked(old('is_home_page',
                                            $category->is_home_page)) />
                                        <label for="is_home_page" class="custom-control-label">Hiển thị trên trang
                                            chủ</label>
                                    </div>
                                </div>
                                {{-- Nhãn HOT/NEW chỉ dùng cho danh mục từ cấp 2 --}}
                                @if(!empty($category->parent_id))
                                <div class="form-group">
                                    <label for="badge">{{ __('Nhãn hiển thị') }}</label>
                                    <select name="badge" id="badge" class="form-control">
                                        <option value="">{{ __('Không có') }}</option>
                                        <option value="hot" @selected(old('badge', $category->badge) == 'hot')>HOT</option>
                                        <option value="new" @selected(old('badge', $category->badge) == 'new')>NEW</option>
                                    </select>
                                    <small class="form-text text-muted">Hiển thị icon HOT/NEW cạnh tên danh mục ở
                                        menu.</small>
                                </div>
                                @endif
                                <div class="form-group">
                                    <label for="excerpt">{{ __('Mô tả') }}</label>
                                    <textarea id="excerpt" name="excerpt" class="form-control" placeholder="Mô tả"
                                        rows="4">{{ old('excerpt', $category->excerpt) }}</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>{{ __('Hình ảnh') }} (logo)</label><br>
                                    
                                    {{-- Hiển thị ảnh cũ nếu có --}}
                                    @php
                                        $imgData = is_string($category->image) ? json_decode($category->image, true) : $category->image;
                                        // Nếu cột image lưu JSON có cấu trúc (main/extra) hoặc lưu chuỗi đường dẫn đơn thuần
                                        $imgSrc = is_array($imgData) ? ($imgData['main'] ?? ($imgData['extra'] ?? '')) : $category->image;
                                    @endphp

                                    @if (!empty($imgSrc))
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $imgSrc) }}" alt="Logo" style="height: 80px; object-fit: contain;">
                                        </div>
                                    @endif

                                    {{-- Input chọn file mới (Không để value ở đây để tránh lỗi HTML) --}}
                                    <input type="file" name="image" id="image" class="form-control-file">
                                </div>
                                @php
                                    $thumbData = json_decode($category->icon, true);
                                    
                                    $mainImage = is_array($thumbData) ? ($thumbData['main'] ?? '') : $category->icon;
                                    $extraImage = is_array($thumbData) ? ($thumbData['extra'] ?? '') : '';
                                @endphp

                                <div class="form-group">
                                    <label>Ảnh Thumb (Trang chủ)</label><br>
                                    <div class="d-flex align-items-center">
                                        <input type="file" name="icon" id="icon" class="@error('icon') is-invalid @enderror">
                                        @if (!empty($mainImage))
                                            <img src="{{ asset('storage/' . $mainImage) }}" alt="Icon" style="height: 80px; margin-left: 10px;">
                                        @endif
                                    </div>
                                    @error('icon')
                                    <span id="icon-error" class="error invalid-feedback d-block">{{ $message }}</span>
                                    @push('scripts')
                                        <script>$(function () { toastr.error(@json($message)); });</script>
                                    @endpush
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Ảnh banner (danh mục)</label><br>
                                    <div class="d-flex align-items-center">
                                        <input type="file" name="icon_extra" id="icon_extra">
                                        @if (!empty($extraImage))
                                            <img src="{{ asset('storage/' . $extraImage) }}" alt="Extra" style="height: 80px; margin-left: 10px;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- KHỐI NHẬP NHIỀU MÃ ƯU ĐÃI (COUPON LIST) CHO TRANG SỬA -->
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Mã ưu đãi') }}</h3>
                                <div class="card-tools">
                                    <button type="button" id="add-coupon-btn" class="btn btn-success btn-sm ml-auto">
                                        <i class="fa fa-plus"></i> {{ __('Thêm mã mới') }}
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="coupon-wrapper">
                                {{-- Lấy dữ liệu từ cột coupon_data, ưu tiên lấy old() nếu validate lỗi --}}
                                @php
                                    $coupons = old('coupon', $category->coupon_data ?? []);
                                @endphp

                                @if(is_array($coupons) && count($coupons) > 0)
                                    @foreach($coupons as $index => $coupon)
                                        <div class="coupon-row card card-body bg-light mb-3">
                                            <div class="row align-items-end">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label>{{ __('Mã Code') }}</label>
                                                        <input type="text" name="coupon[{{ $index }}][code]" class="form-control" placeholder="VD: SALE50" value="{{ $coupon['code'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label>{{ __('Giá trị giảm') }}</label>
                                                        <input type="text" name="coupon[{{ $index }}][discount]" class="form-control" placeholder="VD: 50k hoặc 10%" value="{{ $coupon['discount'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label>{{ __('Đơn tối thiểu') }}</label>
                                                        {{-- Format lại số tiền cho đẹp nếu có sẵn --}}
                                                        @php
                                                            $minSpend = $coupon['min_spend'] ?? '';
                                                            if(is_numeric($minSpend)) {
                                                                $minSpend = number_format($minSpend, 0, '', '.');
                                                            }
                                                        @endphp
                                                        <input type="text" name="coupon[{{ $index }}][min_spend]" class="form-control money-format" placeholder="VD: 200.000" value="{{ $minSpend }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-1 text-right">
                                                    <button type="button" class="btn btn-danger btn-sm remove-coupon-btn">
                                                        <i class="fa fa-trash"></i> Xóa
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="coupon-row card card-body bg-light mb-3">
                                        <div class="row align-items-end">
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>{{ __('Mã Code') }}</label>
                                                    <input type="text" name="coupon[0][code]" class="form-control" placeholder="VD: SALE50">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label>{{ __('Giá trị giảm') }}</label>
                                                    <input type="text" name="coupon[0][discount]" class="form-control" placeholder="VD: 50k hoặc 10%">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>{{ __('Đơn tối thiểu') }}</label>
                                                    <input type="text" name="coupon[0][min_spend]" class="form-control money-format" placeholder="VD: 200.000">
                                                </div>
                                            </div>
                                            <div class="col-md-1 text-right">
                                                <button type="button" class="btn btn-danger btn-sm remove-coupon-btn">
                                                    <i class="fa fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('SEO') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="seo_no_index"
                                            type="checkbox" id="seo_no_index" @checked(old('seo_no_index',
                                            $category->seo_no_index)) />
                                        <label for="seo_no_index" class="custom-control-label">Disable index
                                            search</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seo_title">{{ __('Title') }}</label>
                                    <input type="text" name="seo_title" class="form-control" id="seo_title"
                                        value="{{ old('seo_title', $category->seo_title) }}" />
                                    <p>* <code class="seo_title-character">0</code> ký tự, <code
                                            class="seo_title-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_keywords">{{ __('Keywords') }}</label>
                                    <input type="text" name="seo_keywords" class="form-control" id="seo_keywords"
                                        value="{{ old('seo_keywords', $category->seo_keywords) }}" />
                                    <p>* <code class="seo_keywords-character">0</code> ký tự, <code
                                            class="seo_keywords-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_description">{{ __('Description') }}</label>
                                    <textarea id="seo_description" name="seo_description" class="form-control"
                                        rows="4">{{ old('seo_description', $category->seo_description) }}</textarea>
                                    <p><span style="color: #aeaeae;">Khuyến nghị: 160 ký tự</span> <br> * <code
                                            class="seo_description-character">0</code> ký tự, <code
                                            class="seo_description-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_url_canonical">{{ __('Url canonical') }}</label>
                                    <input type="text" name="seo_url_canonical" class="form-control"
                                        id="seo_url_canonical"
                                        value="{{ old('seo_url_canonical', $category->seo_url_canonical) }}" />
                                </div>
                                <div class="form-group">
                                    <label for="schema">{{ __('Custom Schema (JSON-LD)') }}</label>
                                    @php
                                        $oldSchema = old('schema', isset($category) ? $category->schema : '');
                                    @endphp
                                    <textarea id="schema" name="schema" class="form-control" rows="6">{{ $oldSchema }}</textarea>
                                    <small class="form-text text-muted">Có thể dán trực tiếp toàn bộ thẻ &lt;script type=&quot;application/ld+json&quot;&gt;...&lt;/script&gt; hoặc chỉ đoạn JSON-LD đều được.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.category.index', ['type' => $type]) }}"
                                    class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                    @if($type == App\Enums\CategoryType::PRODUCT->value)
                                        <a href="{{ route('backend.category.preview.edit', ['type' => $type, 'id' => $category->id]) }}" 
                                            target="_blank" 
                                            data-preview-action="{{ route('backend.category.preview.edit', ['type' => $type, 'id' => $category->id]) }}"
                                            class="btn btn-info btn-preview">
                                            {{ __('Xem trước') }}
                                        </a>
                                    @endif
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i
                                        class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i
                                        class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary"><i
                                    class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-primary card-outline" style="position: sticky; top: 60px; z-index: 99999;">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.category.index', ['type' => $type]) }}"
                                    class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                    @if($type == App\Enums\CategoryType::PRODUCT->value)
                                        <a href="{{ route('backend.category.preview.edit', ['type' => $type, 'id' => $category->id]) }}" 
                                            target="_blank" 
                                            data-preview-action="{{ route('backend.category.preview.edit', ['type' => $type, 'id' => $category->id]) }}"
                                            class="btn btn-info btn-preview">
                                            {{ __('Xem trước') }}
                                        </a>
                                    @endif
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i
                                        class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i
                                        class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary"><i
                                    class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}</button>
                            </div>
                        </div>
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Options') }}</h3>
                            </div>
                            <div class="card-body">
                                <!-- Chỉ hiển thị nhóm tùy chọn này nếu là danh mục cấp 2 (có parent_id) -->
                                @php
                                    // Kiểm tra xem đây có đúng là danh mục cấp 2 hay không
                                    $isLevel2 = false;
                                    if (!empty($category->parent_id)) {
                                        // Lấy danh mục cha trực tiếp của danh mục này
                                        $parentCategory = \App\Models\Category::find($category->parent_id);
                                        // Nếu cha tồn tại và cha không có parent_id (tức cha là cấp 1) thì đây là cấp 2
                                        if ($parentCategory && empty($parentCategory->parent_id)) {
                                            $isLevel2 = true;
                                        }
                                    }
                                @endphp

                                @if($isLevel2)
                                    <div class="form-group">
                                        <label>{{ __('Kiểu danh mục') }}</label>
                                        <select class="form-control select2" name="display_type" id="displayTypeSelect">
                                            <option value="attribute" @selected(old('display_type', $category->display_type ?? '') == 'attribute')>
                                                Nhu cầu
                                            </option>
                                            <option value="brand" @selected(old('display_type', $category->display_type ?? '') == 'brand')>
                                                Thương hiệu
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Khối chọn thương hiệu, chỉ hiện khi chọn Thương hiệu -->
                                    <div class="form-group" id="brandContainer" style="display: none;">
                                        <label>{{ __('Chọn thương hiệu') }}</label>
                                        <select class="form-control select2" name="brand_id" style="width: 100%;" data-placeholder="Thương hiệu">
                                            <option value=""></option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" 
                                                    @selected(old('brand_id', $category->brands->first()?->id ?? '') == $brand->id)>
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($category->status->value == $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if($type === "product")
                                    <div class="form-group">
                                        <label for="title">{{ __('Hiện sản phẩm liên quan') }} <code>*</code></label>
                                        <select class="form-control select2" name="status_similar_product">
                                            @foreach ($statusSimilarProduct as $status)
                                                <option value="{{ $status->value }}" @selected(optional($category->status_similar_product)->value == $status->value || $category->status_similar_product == $status->value)>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
    @push('scripts')
    <script>
    $(function() {
        $('.select2').select2();

        $('#name').keyup(function() {
            var title = $(this).val();
            $('#slug').val(convertToSlug(title));
        });

        function convertToSlug(title) {
            slug = title.toLowerCase();
            slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
            slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
            slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
            slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
            slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
            slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
            slug = slug.replace(/đ/gi, 'd');
            slug = slug.replace(
                /\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
            slug = slug.replace(/ /gi, "-");
            slug = slug.replace(/\-\-\-\-\-/gi, '-');
            slug = slug.replace(/\-\-\-\-/gi, '-');
            slug = slug.replace(/\-\-\-/gi, '-');
            slug = slug.replace(/\-\-/gi, '-');
            slug = '@' + slug + '@';
            slug = slug.replace(/\@\-|\-\@|\@/gi, '');
            return slug;
        }

        function countWords(s) {
            s = s.replace(/(^\s*)|(\s*$)/gi, "");
            s = s.replace(/[ ]{2,}/gi, " ");
            s = s.replace(/\n /, "\n");
            return s.split(' ').filter(function(str) {
                return str != "";
            }).length;
        }

        $('#seo_title').keyup(function() {
            var name = $(this).val().trim();
            $('.seo_title-character').text(name.length);
            $('.seo_title-from').text(countWords(name));
        });

        $('#seo_keywords').keyup(function() {
            var name = $(this).val().trim();
            $('.seo_keywords-character').text(name.length);
            $('.seo_keywords-from').text(countWords(name));
        });

        $('#seo_description').keyup(function() {
            var name = $(this).val().trim();
            $('.seo_description-character').text(name.length);
            $('.seo_description-from').text(countWords(name));
        });

        var seoTitle = "{{ $category->seo_title }}";
        var seoKeywords = "{{ $category->seo_keywords }}";
        var seoDescription = "{{ $category->seo_description }}";

        $('.seo_title-character').text(seoTitle.length);
        $('.seo_title-from').text(countWords(seoTitle));
        $('.seo_keywords-character').text(seoKeywords.length);
        $('.seo_keywords-from').text(countWords(seoKeywords));
        $('.seo_description-character').text(seoDescription.length);
        $('.seo_description-from').text(countWords(seoDescription));

        tinymceInit.selector = '#excerpt';
        tinymceInit.file_picker_callback = function(cb, value, meta) {
            var url = "{{ route('backend.photo.store') }}";
            uploadImg(url, cb, value, meta);
        };

        tinymceInit.toolbar_mode = 'wrap';

        // Ghi đè paste_preprocess để xử lý paste trước khi TinyMCE chèn HTML làm hỏng thẻ a
        var existingPastePreprocess = tinymceInit.paste_preprocess;
        tinymceInit.paste_preprocess = function(plugin, args) {
            var editor = tinymce.activeEditor || (plugin ? plugin.editor : null);
            if (editor) {
                var node = editor.selection.getNode();
                var linkEl = editor.dom.getParent(node, 'a.xem-them-link');
                if (!linkEl) {
                    var boxEl = editor.dom.getParent(node, 'div.internal-link-box');
                    if (boxEl) {
                        linkEl = boxEl.querySelector('a.xem-them-link');
                    }
                }

                if (linkEl) {
                    var tmp = document.createElement("DIV");
                    tmp.innerHTML = args.content;
                    var pastedText = tmp.textContent || tmp.innerText || '';
                    pastedText = pastedText.replace(/[\r\n]+/g, " ").trim();

                    if (pastedText) {
                        var slug = typeof convertToSlug === 'function' ? convertToSlug(pastedText) : pastedText.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                        var url = '/' + slug;

                        linkEl.textContent = pastedText;
                        editor.dom.setAttrib(linkEl, 'href', url);

                        setTimeout(function() {
                            if (linkEl.firstChild) {
                                editor.selection.setCursorLocation(linkEl.firstChild, pastedText.length);
                            } else {
                                editor.selection.setCursorLocation(linkEl, 1);
                            }
                        }, 10);
                    }

                    args.content = '';
                    return;
                }
            }

            if (typeof existingPastePreprocess === 'function') {
                existingPastePreprocess(plugin, args);
            }
        };

        // 1. Đưa nút 'internal_link' ra thanh công cụ chính
        tinymceInit.toolbar = tinymceInit.toolbar ? tinymceInit.toolbar + ' internal_link' : 'internal_link';

        var existingSetup = tinymceInit.setup;
        tinymceInit.setup = function (editor) {
            if (typeof existingSetup === 'function') {
                existingSetup(editor);
            }

            editor.ui.registry.addButton('internal_link', {
                    text: 'Xem thêm',
                    tooltip: 'Chèn khung Xem thêm nhanh',
                    onAction: function () {
                        var linkId = 'xem_them_' + Date.now();
                        // Thêm position: relative vào box và chỉnh vị trí chữ x chuẩn xác ở góc trên bên phải
                        var boxHtml = `<div class="internal-link-box" style="position: relative; border-left: 4px solid #007bff; background: #f8f9fa; padding: 12px 30px 12px 15px; margin: 15px 0; font-weight: 500;"><strong style="color: #d60118; margin-right: 5px;">XEM THÊM:</strong><a id="${linkId}" class="xem-them-link" href="" style="text-decoration: none; font-weight: bold;"></a><span class="remove-box-btn" style="position: absolute; top: 8px; right: 10px; cursor: pointer; color: #ff4d4f; font-weight: bold; font-size: 14px; line-height: 1; padding: 2px 5px;" title="Xóa khung này">&times;</span></div>`;

                        editor.insertContent(boxHtml);

                        var linkEl = editor.dom.select('#' + linkId)[0];
                        if (linkEl) {
                            editor.selection.setCursorLocation(linkEl, 0);
                            editor.dom.setAttrib(linkEl, 'id', null);
                            editor.focus();
                        }
                    }
                });

                // Xử lý sự kiện bấm vào nút x để xóa khung
                editor.on('click', function (e) {
                    if (e.target.classList.contains('remove-box-btn')) {
                        var boxEl = editor.dom.getParent(e.target, 'div.internal-link-box');
                        if (boxEl) {
                            editor.dom.remove(boxEl);
                        }
                    }
                });

                var syncLink = function() {
                    var boxes = editor.dom.select('div.internal-link-box');
                    boxes.forEach(function(boxEl) {
                        var strongEl = boxEl.querySelector('strong');
                        var linkEl = boxEl.querySelector('a.xem-them-link');
                        var removeBtn = boxEl.querySelector('.remove-box-btn');

                        // Đảm bảo khung cũ hoặc khung mới tạo đều có nút X chuẩn ở góc
                        if (!removeBtn) {
                            boxEl.style.position = 'relative';
                            boxEl.style.paddingRight = '30px';
                            var span = editor.dom.create('span', {
                                'class': 'remove-box-btn',
                                'style': 'position: absolute; top: 8px; right: 10px; cursor: pointer; color: #ff4d4f; font-weight: bold; font-size: 14px; line-height: 1; padding: 2px 5px;',
                                'title': 'Xóa khung này'
                            }, '&times;');
                            boxEl.appendChild(span);
                        }
                        
                        if (!linkEl) {
                            linkEl = editor.dom.create('a', {
                                'class': 'xem-them-link',
                                'href': '',
                                'style': ''
                            }, '\u00a0');
                            boxEl.appendChild(linkEl);
                        }
                        
                        var child = boxEl.firstChild;
                        var nodesToMove = [];
                        while (child) {
                            if (child !== strongEl && child !== linkEl && !child.classList?.contains('remove-box-btn')) {
                                nodesToMove.push(child);
                            }
                            child = child.nextSibling;
                        }
                        
                        if (nodesToMove.length > 0) {
                            var extraText = '';
                            nodesToMove.forEach(function(node) {
                                extraText += node.textContent;
                                boxEl.removeChild(node);
                            });
                            
                            extraText = extraText.trim();
                            if (extraText.startsWith(':')) {
                                extraText = extraText.slice(1).trim();
                            }
                            
                            if (extraText) {
                                var currentText = linkEl.textContent.replace(/\u00a0/g, '').trim();
                                var newText = currentText + extraText;
                                linkEl.textContent = newText;
                                
                                if (linkEl.firstChild) {
                                    editor.selection.setCursorLocation(linkEl.firstChild, newText.length);
                                }
                            }
                        }
                        
                        var text = linkEl.textContent || '';
                        text = text.replace(/\u00a0/g, ' ').trim();
                        var currentHref = editor.dom.getAttrib(linkEl, 'href');

                        if (text) {
                            if (!currentHref || currentHref === '' || currentHref === '#' ) {
                                var slug = typeof convertToSlug === 'function' ? convertToSlug(text) : text.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                                var url = '/' + slug;
                                editor.dom.setAttrib(linkEl, 'href', url);
                            }
                        } else {
                            editor.dom.setAttrib(linkEl, 'href', '');
                        }
                    });
                };

            // Ngăn chặn xóa hết chữ làm mất thẻ <a> khi nhấn Backspace/Delete
            editor.on('keydown', function (e) {
                if (e.keyCode === 8 || e.keyCode === 46) { // Backspace hoặc Delete
                    var node = editor.selection.getNode();
                    var linkEl = editor.dom.getParent(node, 'a.xem-them-link');
                    if (linkEl) {
                        var text = linkEl.textContent || '';
                        var cleanText = text.replace(/\u00a0/g, '').trim();
                        if (cleanText.length <= 1) {
                            linkEl.innerHTML = '&nbsp;';
                            editor.dom.setAttrib(linkEl, 'href', '');
                            editor.selection.setCursorLocation(linkEl, 0);
                            e.preventDefault();
                        }
                    }
                }
            });
            editor.on('GetContent', function(e) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = e.content;
                    var removeBtns = tmp.querySelectorAll('.remove-box-btn');
                    removeBtns.forEach(function(btn) {
                        btn.remove();
                    });
                    e.content = tmp.innerHTML;
            });

            editor.on('keyup input nodechange', syncLink);
        };
        tinymceInit.content_style = (tinymceInit.content_style || '') + `
            .mce-item-anchor {
                background: none !important;
            }
            .mce-item-anchor::before {
                display: none !important;
            }
            .mce-content-body .mce-item-anchor:not(:empty) {
                padding-left: 5px;
            }
            .mce-content-body [data-mce-selected=inline-boundary] {
                background-color: #b4d7ff !important;
            }
            .img-center {
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
        `;
        tinymceInit.image_class_list = [
                {title: 'Mặc định', value: ''},
                {title: 'Căn giữa', value: 'img-center'}
            ];
            var existingSetupForImg = tinymceInit.setup;
            tinymceInit.setup = function(editor) {
                if (typeof existingSetupForImg === 'function') {
                    existingSetupForImg(editor);
                }
                
                // Tự động thêm class img-center cho mọi thẻ img mới xuất hiện nếu chưa có class gì
                editor.on('NodeChange', function(e) {
                    if (e.element && e.element.nodeName === 'IMG') {
                        if (!e.element.classList.contains('img-center') && !e.element.style.marginLeft) {
                            editor.dom.addClass(e.element, 'img-center');
                        }
                    }
                });
            };

        tinymce.init(tinymceInit);
    });
</script>
    <script>
        $(function() {
            // Xử lý thêm dòng mã ưu đãi mới
            let couponIndex = $('#coupon-wrapper .coupon-row').length;
            
            $('#add-coupon-btn').click(function() {
                let html = `
                    <div class="coupon-row card card-body bg-light mb-3" style="display: none;">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>{{ __('Mã Code') }}</label>
                                    <input type="text" name="coupon[${couponIndex}][code]" class="form-control" placeholder="VD: SALE50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>{{ __('Giá trị giảm') }}</label>
                                    <input type="text" name="coupon[${couponIndex}][discount]" class="form-control" placeholder="VD: 50k hoặc 10%">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>{{ __('Đơn tối thiểu') }}</label>
                                    <input type="text" name="coupon[${couponIndex}][min_spend]" class="form-control money-format" placeholder="VD: 200.000">
                                </div>
                            </div>
                            <div class="col-md-1 text-right">
                                <button type="button" class="btn btn-danger btn-sm remove-coupon-btn">
                                    <i class="fa fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#coupon-wrapper').append(html);
                $('#coupon-wrapper .coupon-row:last').slideDown(200);
                couponIndex++;
            });

            // Xử lý xóa dòng mã ưu đãi
            $(document).on('click', '.remove-coupon-btn', function() {
                let row = $(this).closest('.coupon-row');
                if ($('#coupon-wrapper .coupon-row').length > 1) {
                    row.slideUp(200, function() {
                        $(this).remove();
                    });
                } else {
                    row.find('input').val('');
                }
            });

            // Tự động thêm dấu chấm ngăn cách khi gõ số
            $(document).on('input', '.money-format', function() {                let value = $(this).val().replace(/\D/g, "");
                if (value !== "") {
                    value = new Intl.NumberFormat('vi-VN').format(value);
                }
                $(this).val(value);
            });

            // Trước khi submit form, tự động xóa dấu chấm để lưu vào database dạng số thuần túy
            $('form').on('submit', function() {
                $('.money-format').each(function() {
                    let rawValue = $(this).val().replace(/\./g, '');
                    $(this).val(rawValue);
                });
            });
        });
        </script>

    @if($isLevel2)
    <script>
        $(document).ready(function () {
            var $brandSelect = $('#brandContainer select.select2');

            if (!$brandSelect.hasClass("select2-hidden-accessible")) {
                $brandSelect.select2({
                    placeholder: "Thương hiệu",
                    allowClear: true,
                    width: '100%'
                });
            }

            function toggleBrandContainer() {
                var selectedType = $('[name="display_type"]').val();
                if (selectedType === 'brand') {
                    $('#brandContainer').show();
                    $brandSelect.trigger('change.select2');
                } else {
                    $('#brandContainer').hide();
                }
            }

            toggleBrandContainer();

            $('[name="display_type"]').on('change select2:select', function () {
                toggleBrandContainer();
            });
        });
    </script>
    @endif

    @endpush
</x-app-layout>
