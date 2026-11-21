<x-app-layout>
    <style>
        .tox .tox-toolbar--scrolling {
                flex-wrap: wrap !important;
        }
    </style>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo mới sản phẩm') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.product.index') }}">{{ __('Danh sách sản phẩm') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo mới') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.product.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin chung') }}</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">{{ __('Tên sản phẩm') }} <code>*</code></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Tên sản phẩm" class="form-control @error('name') is-invalid @enderror" />
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="Đường dẫn" class="form-control @error('slug') is-invalid @enderror" />
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="description">{{ __('Mô tả') }}</label>
                                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Mô tả" rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                    <span id="description-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="images">{{ __('Hình ảnh') }} <code>*</code></label>
                                    <div class="image_dropzone_wrapper">
                                        <textarea id="images" name="images" class="form-control d-none @error('images') is-invalid @enderror">{{ old('images') }}</textarea>
                                        <div class="dropzone-upload">
                                            <div id="images-image" class="dropzone needsclick dz-clickable" data-content_input="images" data-primary="true" data-route="{{ route('backend.dropzone.uploadImage') }}" data-token="{{ csrf_token() }}" data-default_values="{{ old('images') }}">
                                                <div class="dz-message needsclick">Kéo thả file vào đây</div>

                                                <button type="button" class="btn btn-primary d-block ml-auto mr-auto file_manager_button" data-file_type="image" data-input="images" data-preview="prev-images" data-route="{{ route('backend.file-manager.index') }}" data-site_url="{{ route('backend.dashboard') }}">
                                                    Hoặc chọn ảnh có sẵn <i class="far fa-image"></i>
                                                </button>
                                                <div class="dropzone-previews needsclick"></div>
                                            </div>
                                        </div>
                                        @error('images')
                                        <span id="images-error" class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="content">{{ __('Nội dung') }} <code>*</code></label>
                                    <button type="button" class="btn btn-sm btn-primary ml-3 btn-tinymce-toggle-image">Thêm ảnh</button>
                                    <div class="wrapper-tinymce-use-image">
                                        <div class="wrapper-tinymce-image dropzone-upload" style="display: none;">
                                            <div id="body-image" class="dropzone needsclick dz-clickable" data-content_type="post" data-content_id="0" data-route="{{ route('backend.dropzone.uploadImage', ['image_scope' => 'content']) }}" data-token="{{ csrf_token() }}" data-default_values="{{ old('images') }}">
                                                <div class="dz-message needsclick">Kéo thả file vào đây</div>
                                                <div class="dropzone-previews needsclick"></div>
                                            </div>
                                        </div>
                                        <div class="wrapper-tinymce-tinymce">
                                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" placeholder="Nội dung">{{ old('content') }}</textarea>
                                            @error('content')
                                            <span id="content-error" class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Youtube Id') }}</label>
                                    <input type="text" name="youtube_id" id="youtube_id" value="{{ old('youtube_id') }}" placeholder="Youtube Id" class="form-control @error('youtube_id') is-invalid @enderror" />
                                </div>
                            </div>
                        </div>

                        @include('backend.product._promotion-gifts', [
                            'promotionValue' => old('promotion'),
                        ])

                        <div class="card card-primary card-outline">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sku">{{ __('SKU') }}</label>
                                            <input type="text" name="sku" class="form-control" id="sku" value="{{ old('sku') }}" />
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity">{{ __('Số lượng') }}</label>
                                            <input type="number" name="quantity" class="form-control" id="quantity" value="{{ old('quantity') }}" />
                                        </div>
                                        <div class="form-group">
                                            <label for="warranty">Số tháng bảo hành</label>
                                            <input type="number" name="warranty" class="form-control" id="warranty" value="{{ old('warranty') }}" />
                                        </div>
                                        <div class="form-group">
                                            <label for="regular_price">{{ __('Giá bán thường') }}</label>
                                            <input type="text" name="regular_price" class="form-control" id="regular_price" value="{{ old('regular_price') }}" />
                                        </div>
                                        <div class="form-group">
                                            <label for="sale_price">{{ __('Giá khuyến mãi') }}</label>
                                            <input type="text" name="sale_price" class="form-control" id="sale_price" value="{{ old('sale_price') }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="model">{{ __('Model') }}</label>
                                            <input type="text" name="model" class="form-control" id="model" value="{{ old('model') }}" />
                                        </div>
                                        <div class="form-group">
                                            <label for="flash_sale_price">{{ __('Trạng thái kho hàng') }}</label>
                                            <select class="form-control select2" name="stock_status">
                                                @foreach ($stockStatusList as $stockStatus)
                                                <option value="{{ $stockStatus->value }}" @selected(old('stock_status')==$stockStatus->value)>
                                                    {{ $stockStatus->label() }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- <div class="form-group">
                                            <label for="timeout">{{ __('Thời gian hết hạn') }}</label>
                                            <div class="input-group date" id="timeout" data-target-input="nearest">
                                                <input type="text" name="timeout" value="{{ old('timeout') }}" placeholder="VD: 2023-02-08 08:31:38" class="form-control datetimepicker-input" data-target="#timeout" />
                                                <div class="input-group-append" data-target="#timeout" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div> -->
                                        <div class="form-group">
                                            <label for="title">{{ __('VAT') }}</label>
                                            <select class="form-control select2" name="is_vat">
                                                @foreach ($vatList as $vat)
                                                <option value="{{ $vat->value }}" @selected(old('is_vat')==$vat->value)>
                                                    {{ $vat->label() }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity">{{ __('Tùy chọn') }}</label>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" value="1" name="is_new" type="checkbox" id="is_new" @checked(old('is_new')) />
                                                <label for="is_new" class="custom-control-label">{{ __('Mới') }}</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" value="1" name="is_hot" type="checkbox" id="is_hot" @checked(old('is_hot')) />
                                                <label for="is_hot" class="custom-control-label">{{ __('Best seller') }}</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" value="1" name="is_flash_sale" type="checkbox" id="is_flash_sale" @checked(old('is_flash_sale')) />
                                                <label for="is_flash_sale" class="custom-control-label">{{ __('Flash sale') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('SEO') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="seo_no_index" type="checkbox" id="seo_no_index" @checked(old('seo_no_index')) />
                                        <label for="seo_no_index" class="custom-control-label">{{ __('Disable index search') }}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seo_title">{{ __('Title') }}</label>
                                    <input type="text" name="seo_title" class="form-control" id="seo_title" value="{{ old('seo_title') }}" />
                                    <p>* <code class="seo_title-character">0</code> ký tự, <code class="seo_title-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_keywords">{{ __('Keywords') }}</label>
                                    <input type="text" name="seo_keywords" class="form-control" id="seo_keywords" value="{{ old('seo_keywords') }}" />
                                    <p>* <code class="seo_keywords-character">0</code> ký tự, <code class="seo_keywords-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_description">{{ __('Description') }}</label>
                                    <textarea id="seo_description" name="seo_description" class="form-control" rows="4">{{ old('seo_description') }}</textarea>
                                    <p><span style="color: #aeaeae;">Khuyến nghị: 160 ký tự</span> <br> * <code class="seo_description-character">0</code> ký tự, <code class="seo_description-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_url_canonical">{{ __('Url canonical') }}</label>
                                    <input type="text" name="seo_url_canonical" class="form-control" id="seo_url_canonical" value="{{ old('seo_url_canonical') }}" />
                                </div>
                                <div class="form-group">
                                    <label for="schema">{{ __('Custom Schema (JSON-LD)') }}</label>
                                    @php
                                        $oldSchema = old('schema', isset($product) ? $product->schema : '');
                                    @endphp
                                    <textarea id="schema" name="schema" class="form-control" rows="6">{{ $oldSchema }}</textarea>
                                    <small class="form-text text-muted">Có thể dán trực tiếp toàn bộ thẻ &lt;script type=&quot;application/ld+json&quot;&gt;...&lt;/script&gt; hoặc chỉ đoạn JSON-LD đều được.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.product.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary"><i class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}</button>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="card card-primary card-outline" style="position: sticky; top: 60px; z-index: 99999;">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.product.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary"><i class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}</button>
                            </div>
                        </div>
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Options') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="category_ids[]">{{ __('Danh mục') }}</label>
                                    <div class="product_category_wrap">
                                        <ul class="mb-0">
                                            @foreach ($categories as $category)
                                            <li class="product_category_item">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="category_ids[]" id="category-{{ $category->id }}" value="{{ $category->id }}" class="custom-control-input"  {{ $category->inCategoryIds(old('category_ids')) }}>
                                                    <label for="category-{{ $category->id }}" class="custom-control-label form-check-label product_category_label">
                                                        {{ $category->name }}
                                                    </label>
                                                    @if(count($category->childs))
                                                    <span class="product_category_wrap_item_span">
                                                        <i class="fas fa-plus"></i>
                                                        <i class="fas fa-minus"></i>
                                                    </span>
                                                    @endif
                                                </div>
                                                @if(count($category->childs))
                                                    <ul class="wrapper-children open">
                                                    @include('backend.category.child.add-multiple', [
                                                        'childs' => $category->childs,
                                                    ])
                                                    </ul>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Phụ kiện thường mua cùng <small class="text-muted">(tick = đưa sản phẩm đó vào danh mục Phụ kiện của nhánh)</small></label>
                                    <div id="accessoryPicker" class="border rounded p-2" style="max-height:220px;overflow-y:auto;"
                                         data-exclude-id="0">
                                        <p class="text-muted small mb-0">Tick danh mục ở trên để chọn phụ kiện</p>
                                    </div>
                                </div>
                                 <div class="form-group">
                                    <label for="title">Thương hiệu</label>
                                    <select class="form-control select2" name="brand_id">
                                        <option value="0">Không có thương hiệu</option>
                                        @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>
                                            {{ $brand->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Thứ tự') }}</label>
                                    <input type="text" name="order" class="form-control" id="order" value="{{ old('order', 0) }}" />
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }}</label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected(old('status')==$status->value)>
                                            {{ $status->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    @push('backend-css')
    <link rel="stylesheet" href="{{ asset('css/backend/dropzone.min.css') }}">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="{{ asset('js/backend/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/backend/dropzone.js') }}"></script>
    <script src="{{ asset('js/backend/stand-alone-button.js') }}"></script>
    <script src="{{ asset('js/backend/promotion-gifts.js') }}?v={{ filemtime(public_path('js/backend/promotion-gifts.js')) }}"></script>
    <script>
        $(function() {
            $('.select2').select2();
            $('#timeout').datetimepicker({
                icons: {
                    time: 'far fa-clock'
                },
                timePicker: true,
                format: 'YYYY-MM-DD hh:mm:ss'
            });
            $('#description').summernote({
                height: 150,
                callbacks: {
                    onImageUpload: function(files) {
                        uploadImage(files[0]);
                    }
                }
            });

            tinymceInit.toolbar_mode = 'wrap';

            // Ghi đè paste_preprocess để xử lý paste khung Xem thêm trên product
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

            // Đưa nút 'internal_link' ra thanh công cụ chính
            tinymceInit.toolbar = tinymceInit.toolbar ? tinymceInit.toolbar + ' internal_link' : 'internal_link';

            var existingSetup = tinymceInit.setup;
            tinymceInit.setup = function (editor) {
                if (typeof existingSetup === 'function') {
                    existingSetup(editor);
                }

                // Đăng ký nút bấm chèn nhanh khung Xem thêm
                // Đăng ký nút bấm chèn nhanh khung Xem thêm
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

                editor.on('keydown', function (e) {
                    if (e.keyCode === 8 || e.keyCode === 46) {
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

            // Khởi tạo TinyMCE cho #content
            tinymceInit.selector = '#content';
            tinymceInit.file_picker_callback = function(cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };

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

            // Khởi tạo TinyMCE cho #promotion.
            // Dùng BẢN SAO config riêng thay vì sửa tiếp tinymceInit: editor #content ở trên
            // đã init xong nên hành vi ảnh trong bài viết (nút "Xem thêm", class img-center)
            // giữ nguyên 100%. Bắt buộc phải tách vì setup hiện tại gắn class img-center
            // (display:block + margin auto) cho MỌI thẻ IMG -> ảnh quà tặng trong danh sách
            // khuyến mãi sẽ bị biến thành block căn giữa, không nằm cạnh chữ được.
            var promoInit = Object.assign({}, tinymceInit);
            promoInit.selector = '#promotion';
            promoInit.height = 200;
            delete promoInit.setup;
            promoInit.toolbar = (tinymceInit.toolbar || '').replace(/\s*\binternal_link\b/g, '');
            promoInit.image_class_list = [{title: 'Mặc định', value: ''}];
            promoInit.file_picker_callback = function(cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };
            tinymce.init(promoInit);

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
                s = s.replace(/(^\s*)|(\s*$)/gi, ""); //exclude  start and end white-space
                s = s.replace(/[ ]{2,}/gi, " "); //2 or more space to 1
                s = s.replace(/\n /, "\n"); // exclude newline with a start spacing
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
        });

        if (typeof LwmAdminDropzone == 'function') {
            let imageDropzoneimages = new LwmAdminDropzone();
            imageDropzoneimages.init('#images-image');
        }

        if (typeof $.fn.filemanager === 'function') {
            $('button.file_manager_button').each(function() {
                let type = $(this).data('file_type') || 'image';
                $(this).filemanager(type);
            });
            $(document).on('click', '.trash', function() {
                let target_input = $('#' + $(this).closest('.file_manager_wrapper').data('input')),
                    file_item = $(this).closest('.file_manager_item'),
                    item_url = $(file_item).find('a').attr('href'),
                    json_value = target_input.val() !== '' ? JSON.parse(target_input.val()) : [];

                json_value = json_value.filter(function(item) {
                    return item.url !== item_url;
                });

                if (json_value.length === 0) {
                    target_input.val('');
                } else {
                    target_input.val(JSON.stringify(json_value));
                }

                file_item.remove();
                $('.file_manager_lists').attr('data-count', json_value.length);
            });
            $(document).on('click', '.primary-image', function() {
                let wrapper = $(this).closest('.file_manager_wrapper'),
                    target_input = $('#' + wrapper.data('input')),
                    file_item = $(this).closest('.file_manager_item'),
                    item_url = $(file_item).find('a').attr('href'),
                    json_value = target_input.val() !== '' ? JSON.parse(target_input.val()) : [];

                json_value = json_value.map(function(item) {
                    item.primary = item_url === item.url;
                    return item;
                });
                if (json_value.length === 0) {
                    target_input.val('');
                } else {
                    target_input.val(JSON.stringify(json_value));
                }

                $('.file_manager_lists').attr('data-count', json_value.length);
                wrapper.find('.primary-image').removeClass('active');
                $(this).addClass('active');
            });
        }

        if (typeof LwmAdminDropzone == 'function') {
            let tinymceDropzonebody = new LwmAdminDropzone();
            tinymceDropzonebody.init('#body-image', 'content');
        }
        $('.btn-tinymce-toggle-image').on('click', function () {
            $('.wrapper-tinymce-image').animate({width: 'toggle'}, "slow");
        });

        const previewContainer = document.querySelector('#images-image .dropzone-previews');
        if (previewContainer) {
            Sortable.create(previewContainer, {
                animation: 150,
                onEnd: function () {
                    let images = [];
                    $('#images-image .dz-preview').each(function () {
                        let url = $(this).attr('data-url') || $(this).find('img').attr('src');
                        let name = $(this).attr('data-name') || '';
                        let thumb_url = $(this).attr('data-thumb_url') || '';
                        let primary = $(this).find('.dz-primary').hasClass('default');

                        if (url) {
                            images.push({
                                name: name,
                                url: url,
                                thumb_url: thumb_url,
                                primary: primary
                            });
                        }
                    });

                    $('#images').val(JSON.stringify(images));
                }
            });
        }

        $('#regular_price').number(true);
        $('#sale_price').number(true);

        $('#regular_price').keyup(function() {
            toggleSalePrice();
        });

        toggleSalePrice();

        function toggleSalePrice() {
            let regular = $('#regular_price').val();

            if (!regular || parseInt(regular) === 0) {
                $("#sale_price").val('');
                $("#sale_price").prop('disabled', true);
            } else {
                $("#sale_price").prop('disabled', false);
            }
        }
    </script>
    <script>
        $(document).ready(function() {
        $(document).on('change', 'input[name="category_ids[]"]', function() {
            let isChecked = $(this).prop('checked');
            let $currentItem = $(this).closest('li.product_category_item');
            
            if (isChecked) {
                // 1. TÍCH CHỌN: Nếu tích con thì các cha đi lên trên cũng được tích theo
                $currentItem.parents('li.product_category_item').each(function() {
                    let $parentCheckbox = $(this).children('.custom-control').find('input[name="category_ids[]"]');
                    $parentCheckbox.prop('checked', true);
                });
            } else {
                // 2. BỎ TÍCH: Nếu bỏ tích cha thì tất cả các con/cháu bên trong nó cũng mất tích luôn
                $currentItem.find('input[name="category_ids[]"]').prop('checked', false);
            }
        });
    });
    </script>
    <script>
        $(document).ready(function() {
            var $picker = $('#accessoryPicker');
            if (!$picker.length) return;

            var cache = {};
            var url = "{{ route('backend.product.accessory-candidates') }}";

            function renderAccessoryPicker(res) {
                var items = res.items || [];
                if (!items.length) {
                    var msg = res.has_accessory_category
                        ? 'Chưa có sản phẩm nào trong danh mục phụ kiện'
                        : 'Không tìm thấy danh mục con "Phụ kiện" của danh mục đã chọn';
                    $picker.html('<p class="text-muted small mb-0">' + msg + '</p>');
                    return;
                }
                var html = '';
                items.forEach(function(item) {
                    var checked = item.checked ? ' checked' : '';
                    var price = item.price > 0 ? Number(item.price).toLocaleString('vi-VN') + 'đ' : 'Liên hệ';
                    html += '<div class="custom-control custom-checkbox">'
                        + '<input type="checkbox" name="accessory_ids[]" id="accessory-' + item.id + '" value="' + item.id + '" class="custom-control-input"' + checked + '>'
                        + '<label for="accessory-' + item.id + '" class="custom-control-label form-check-label">' + item.name + ' — ' + price + '</label>'
                        + '</div>';
                });
                $picker.html(html);
            }

            function loadAccessoryPicker() {
                var ids = $('input[name="category_ids[]"]:checked').map(function() { return this.value; }).get();
                if (!ids.length) {
                    $picker.html('<p class="text-muted small mb-0">Tick danh mục ở trên để chọn phụ kiện</p>');
                    return;
                }
                var key = ids.join(',');
                if (cache[key]) {
                    renderAccessoryPicker(cache[key]);
                    return;
                }
                $.getJSON(url, { category_ids: ids, exclude_id: $picker.data('exclude-id') }, function(res) {
                    cache[key] = res;
                    renderAccessoryPicker(res);
                });
            }

            $(document).on('change', 'input[name="category_ids[]"]', loadAccessoryPicker);
            loadAccessoryPicker();
        });
    </script>
    @endpush
</x-app-layout>