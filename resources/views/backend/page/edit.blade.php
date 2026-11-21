<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Cập nhật trang') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Quản lý trang') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.page.update', ['id' => $page->id]) }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin trang') }}</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">{{ __('Tiêu đề') }} <code>*</code></label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" placeholder="Tiêu đề" class="form-control @error('title') is-invalid @enderror" />
                                    @error('title')
                                    <span id="title-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="Đường dẫn" class="form-control @error('slug') is-invalid @enderror" />
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="content">{{ __('Nội dung') }} <code>*</code></label>
                                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" placeholder="Nội dung">{{ old('content', $page->content) }}</textarea>
                                    @error('content')
                                    <span id="content-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
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
                                        <input class="custom-control-input" value="1" name="seo_no_index" type="checkbox" id="seo_no_index" @checked(old('seo_no_index', $page->seo_no_index)) />
                                        <label for="seo_no_index" class="custom-control-label">Disable index search</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seo_title">{{ __('Title') }}</label>
                                    <input type="text" name="seo_title" class="form-control" id="seo_title" value="{{ old('seo_title', $page->seo_title) }}" />
                                    <p>* <code class="seo_title-character">0</code> ký tự, <code class="seo_title-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_keywords">{{ __('Keywords') }}</label>
                                    <input type="text" name="seo_keywords" class="form-control" id="seo_keywords" value="{{ old('seo_keywords', $page->seo_keywords) }}" />
                                    <p>* <code class="seo_keywords-character">0</code> ký tự, <code class="seo_keywords-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_description">{{ __('Description') }}</label>
                                    <textarea id="seo_description" name="seo_description" class="form-control" rows="4">{{ old('seo_description', $page->seo_description) }}</textarea>
                                    <p><span style="color: #aeaeae;">Khuyến nghị: 160 ký tự</span> <br> * <code class="seo_description-character">0</code> ký tự, <code class="seo_description-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_url_canonical">{{ __('Url canonical') }}</label>
                                    <input type="text" name="seo_url_canonical" class="form-control" id="seo_url_canonical" value="{{ old('seo_url_canonical', $page->seo_url_canonical) }}" />
                                </div>
                                {{-- Ô NHẬP SCHEMA (CUSTOM JSON-LD) ĐƯỢC ĐẶT Ở ĐÂY --}}
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
                                <a href="{{ route('backend.page.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-primary card-outline" style="position: sticky; top: 60px; z-index: 99999;">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.page.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Options') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        <option value="{{ App\Enums\Status::ACTIVED->value }}" {{ $page->status == App\Enums\Status::ACTIVED->value ? 'selected="selected"' : ''}}>Hiển thị</option>
                                        <option value="{{ App\Enums\Status::INACTIVED->value }}" {{ $page->status == App\Enums\Status::INACTIVED->value ? 'selected="selected"' : ''}}>Ẩn</option>
                                    </select>
                                </div>
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

            tinymceInit.selector = '#content';
            tinymceInit.file_picker_callback = function (cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };
            tinymce.init(tinymceInit);

            $('#title').keyup(function() {
                var name = $(this).val();
                $('#slug').val(convertToSlug(name));
            });

            function convertToSlug(name) {
                slug = name.toLowerCase();
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

            var seoTitle = "{{ $page->seo_title }}";
            var seoKeywords = "{{ $page->seo_keywords }}";
            var seoDescription = "{{ $page->seo_description }}";

            $('.seo_title-character').text(seoTitle.length);
            $('.seo_title-from').text(countWords(seoTitle));

            $('.seo_keywords-character').text(seoKeywords.length);
            $('.seo_keywords-from').text(countWords(seoKeywords));

            $('.seo_description-character').text(seoDescription.length);
            $('.seo_description-from').text(countWords(seoDescription));
        });
    </script>
    @endpush
</x-app-layout>