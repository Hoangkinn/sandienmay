<x-app-layout>
<style>
        .tox .tox-toolbar--scrolling {
                flex-wrap: wrap !important;
        }
    </style>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 d-flex align-items-center">
                    <h1 class="m-0">{{ __('Cập nhật tin tức') }}</h1>
                    <ol class="breadcrumb ml-auto">
                        <li class="breadcrumb-item">
                            <a href="{{ route('frontend.slug', ['slug' => $post->slug]) }}" class="btn btn-xs btn-outline-danger">{{ __('Xem trên web') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.post.index') }}" class="btn btn-xs btn-outline-danger">{{ __('Danh sách tin tức') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.post.create') }}" class="btn btn-xs btn-outline-danger">{{ __('Thêm mới') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a onclick="return confirm('Bạn muốn xóa bài viết này?')" href="{{ route('backend.post.delete', ['id' => $post->id]) }}" class="btn btn-xs btn-outline-danger">{{ __('Xóa') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.post.update', ['id' => $post->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin bài viết') }}</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">{{ __('Tiêu đề') }} <code>*</code></label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" placeholder="Tiêu đề" class="form-control @error('title') is-invalid @enderror" />
                                    @error('title')
                                    <span id="title-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group" style="display: none">
                                    <label for="excerpt">{{ __('Mô tả ngắn') }} <code>*</code></label>
                                    <textarea id="excerpt" name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Mô tả" rows="4">{{ old('excerpt', $post->excerpt) }}</textarea>
                                    @error('excerpt')
                                    <span id="excerpt-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="photo">{{ __('Hình ảnh') }}</label> <br>
                                    <input type="file" name="image" id="photo" value="{{ old('image', $post->image) }}" class="@error('image') is-invalid @enderror" />
                                    @error('image')
                                    <span id="photo-error" class="error invalid-feedback">{{ $message }}</span>
                                    @push('scripts')
                                        <script>$(function () { toastr.error(@json($message)); });</script>
                                    @endpush
                                    @enderror
                                    <br>
                                    <br>
                                    <img src="{{asset('storage/'.$post->image )}}" title="{{ $post->title }}" class="img-size-64" />
                                </div>
                                <div class="form-group">
                                    <label for="content">{{ __('Nội dung') }} <code>*</code></label>
                                    <button type="button" class="btn btn-sm btn-primary ml-3 btn-tinymce-toggle-image">Thêm ảnh</button>
                                    <div class="wrapper-tinymce-use-image">
                                        <div class="wrapper-tinymce-image dropzone-upload" style="display: none;">
                                            <div id="body-image" class="dropzone needsclick dz-clickable" data-content_type="post" data-content_id="{{ $post->id }}" data-route="{{ route('backend.dropzone.uploadImage', ['image_scope' => 'content']) }}" data-token="{{ csrf_token() }}" data-default_values="{{ old('images') }}">
                                                <div class="dz-message needsclick">Kéo thả file vào đây</div>
                                                <div class="dropzone-previews needsclick"></div>
                                            </div>
                                        </div>
                                        <div class="wrapper-tinymce-tinymce">
                                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" placeholder="Nội dung">{{ old('content', $post->content) }}</textarea>
                                            @error('content')
                                            <span id="content-error" class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
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
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="Đường dẫn" class="form-control @error('slug') is-invalid @enderror" />
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="seo_no_index" type="checkbox" id="seo_no_index" @checked(old('seo_no_index', $post->seo_no_index)) />
                                        <label for="seo_no_index" class="custom-control-label">Disable index search</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seo_title">{{ __('Title') }}</label>
                                    <input type="text" name="seo_title" class="form-control" id="seo_title" value="{{ old('seo_title', $post->seo_title) }}" />
                                    <p>* <code class="seo_title-character">0</code> ký tự, <code class="seo_title-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_keywords">{{ __('Keywords') }}</label>
                                    <input type="text" name="seo_keywords" class="form-control" id="seo_keywords" value="{{ old('seo_keywords', $post->seo_keywords) }}" />
                                    <p>* <code class="seo_keywords-character">0</code> ký tự, <code class="seo_keywords-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_description">{{ __('Description') }}</label>
                                    <textarea id="seo_description" name="seo_description" class="form-control" rows="4">{{ old('seo_description', $post->seo_description) }}</textarea>
                                    <p><span style="color: #aeaeae;">Khuyến nghị: 160 ký tự</span> <br> * <code class="seo_description-character">0</code> ký tự, <code class="seo_description-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_url_canonical">{{ __('Url canonical') }}</label>
                                    <input type="text" name="seo_url_canonical" class="form-control" id="seo_url_canonical" value="{{ old('seo_url_canonical', $post->seo_url_canonical) }}" />
                                </div>
                                <div class="form-group">
                                    <label for="schema">{{ __('Custom Schema (JSON-LD)') }}</label>
                                    @php
                                        $oldSchema = old('schema', isset($post) ? $post->schema : '');
                                    @endphp
                                    <textarea id="schema" name="schema" class="form-control" rows="6">{{ $oldSchema }}</textarea>
                                    <small class="form-text text-muted">Có thể dán trực tiếp toàn bộ thẻ &lt;script type=&quot;application/ld+json&quot;&gt;...&lt;/script&gt; hoặc chỉ đoạn JSON-LD đều được.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.post.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left mr-1"></i>{{ __('Quay lại') }}
                                </a>
                                <a href="{{ route('backend.post.preview', $post->id) }}" target="_blank" data-preview-action="{{ route('backend.post.preview', $post->id) }}" class="btn btn-info btn-preview">
                                    <i class="fa fa-eye mr-1"></i>{{ __('Xem trước') }}
                                </a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary">
                                    <i class="fa fa-save mr-1"></i>{{ __('Lưu') }}
                                </button> --}}
                                
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success">
                                    <i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}
                                </button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary">
                                    <i class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-primary card-outline" style="position: sticky; top: 60px; z-index: 99999;">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.post.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left mr-1"></i>{{ __('Quay lại') }}
                                </a>
                                <a href="{{ route('backend.post.preview', $post->id) }}" target="_blank" data-preview-action="{{ route('backend.post.preview', $post->id) }}" class="btn btn-info btn-preview">
                                    <i class="fa fa-eye mr-1"></i>{{ __('Xem trước') }}
                                </a>
                                
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary">
                                    <i class="fa fa-save mr-1"></i>{{ __('Lưu') }}
                                </button> --}}
                                
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success">
                                    <i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}
                                </button>
                                <button type="submit" name="submit" value="save_exit" data-save-exit data-frontend-template="{{ route('frontend.slug', ['slug' => '__SLUG__']) }}" class="btn btn-primary">
                                    <i class="fa fa-external-link-alt mr-1"></i>{{ __('Lưu và thoát') }}
                                </button>
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
                                                    <input type="checkbox" name="category_ids[]" id="category-{{ $category->id }}" value="{{ $category->id }}" class="custom-control-input" {{ $category->isSelectedByType($post->id, App\Enums\CategoryType::POST->value) }}>
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
                                                <ul class="wrapper-children">
                                                    @include('backend.category.child.edit-multiple', [
                                                        'childs' => $category->childs,
                                                        'id' => $post->id,
                                                        'type' => App\Enums\CategoryType::POST->value,
                                                    ])
                                                </ul>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Thứ tự') }}</label>
                                    <input type="text" name="order" class="form-control" id="order" value="{{ old('order', $post->order) }}" />
                                </div>
                                <div class="form-group">
    <label for="status">{{ __('Trạng thái') }}</label>
    <select class="form-control select2" name="status">
        @foreach ($statusList as $status)
            <option value="{{ $status->value }}" 
                @selected(old('status', $post->status->value ?? $post->status) == $status->value)>
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
    <script src="{{ asset('js/backend/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/backend/dropzone.js') }}"></script>
    <script src="{{ asset('js/backend/stand-alone-button.js') }}"></script>
    <script>
        $(function() {
            $('.select2').select2();

            $('#title').keyup(function() {
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
                slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
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

            tinymceInit.selector = '#content';
            tinymceInit.file_picker_callback = function(cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };

            
            tinymceInit.toolbar_mode = 'wrap';

            // Ghi đè paste_preprocess để xử lý paste khung Xem thêm trên post
            // Ghi đè paste_preprocess để xử lý paste khung Xem thêm trên post
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
                            // Kiểm tra nếu người dùng paste cả một đường dẫn URL
                            if (pastedText.startsWith('http://') || pastedText.startsWith('https://') || pastedText.startsWith('/')) {
                                // Nếu là URL, ta có thể tách lấy slug hoặc gán thẳng vào href, giữ nguyên text hiện tại của link hoặc xử lý tùy ý
                                editor.dom.setAttrib(linkEl, 'href', pastedText);
                            } else {
                                // Nếu là text tiêu đề bình thường
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
                        }

                        // Chặn nội dung mặc định của sự kiện paste để không bị nhân đôi
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

        if (typeof LwmAdminDropzone == 'function') {
            let tinymceDropzonebody = new LwmAdminDropzone();
            tinymceDropzonebody.init('#body-image', 'content');
        }
        $('.btn-tinymce-toggle-image').on('click', function () {
            $('.wrapper-tinymce-image').animate({width: 'toggle'}, "slow");
        });
    </script>
    <script>
        $(document).ready(function() {
        // Khi click vào checkbox bất kỳ trong cây danh mục
        $(document).on('change', '.product_category_item input[type="checkbox"]', function() {
            var $currentCheckbox = $(this);
            
            if ($currentCheckbox.is(':checked')) {
                // Tìm tất cả các thẻ ul cha gần nhất đi ngược lên trên
                // để check luôn các danh mục cha cấp trên của nó
                $currentCheckbox.parents('li.product_category_item').each(function() {
                    $(this).children('.custom-control').find('input[type="checkbox"]').prop('checked', true);
                });
            }
        });
    });
    </script>
    @endpush
</x-app-layout>