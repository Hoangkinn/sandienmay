<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Cập nhật dự án') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.project.index') }}">{{ __('Danh sách dự án') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Cập nhật dự án') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.project.update', ['id' => $project->id]) }}" enctype="multipart/form-data">
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
                                    <label for="name">{{ __('Tên dự án') }} <code>*</code></label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" placeholder="Tên dự án" class="form-control @error('name') is-invalid @enderror" />
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug', $project->slug) }}" placeholder="Đường dẫn" class="form-control @error('slug') is-invalid @enderror" />
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="images">{{ __('Hình ảnh') }} <code>*</code></label>
                                    <div class="image_dropzone_wrapper">
                                        <textarea id="images" name="images" class="form-control d-none @error('images') is-invalid @enderror">{{ $images }}</textarea>
                                        <div class="dropzone-upload">
                                            <div id="images-image" class="dropzone needsclick dz-clickable" data-content_input="images" data-primary="true" data-route="{{ route('backend.dropzone.uploadImage') }}" data-token="{{ csrf_token() }}" data-default_values="{{ $images }}">
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
                                    <label for="description">{{ __('Mô tả ngắn') }}</label>
                                    <textarea id="description" name="description" class="form-control" placeholder="Mô tả" rows="4">{{ old('description', $project->description) }}</textarea>
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
                                        <input class="custom-control-input" value="1" name="seo_no_index" type="checkbox" id="seo_no_index" @checked(old('seo_no_index', $project->seo_no_index)) />
                                        <label for="seo_no_index" class="custom-control-label">Disable index search</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="seo_title">{{ __('Title') }}</label>
                                    <input type="text" name="seo_title" class="form-control" id="seo_title" value="{{ old('seo_title', $project->seo_title) }}" />
                                    <p>* <code class="seo_title-character">0</code> ký tự, <code class="seo_title-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_keywords">{{ __('Keywords') }}</label>
                                    <input type="text" name="seo_keywords" class="form-control" id="seo_keywords" value="{{ old('seo_keywords', $project->seo_keywords) }}" />
                                    <p>* <code class="seo_keywords-character">0</code> ký tự, <code class="seo_keywords-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_description">{{ __('Description') }}</label>
                                    <textarea id="seo_description" name="seo_description" class="form-control" rows="4">{{ old('seo_description', $project->seo_description) }}</textarea>
                                    <p><span style="color: #aeaeae;">Khuyến nghị: 160 ký tự</span> <br> * <code class="seo_description-character">0</code> ký tự, <code class="seo_description-from">0</code> từ</p>
                                </div>
                                <div class="form-group">
                                    <label for="seo_url_canonical">{{ __('Url canonical') }}</label>
                                    <input type="text" name="seo_url_canonical" class="form-control" id="seo_url_canonical" value="{{ old('seo_url_canonical', $project->seo_url_canonical) }}" />
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.project.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.project.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                    <label for="title">{{ __('Thứ tự') }}</label>
                                    <input type="text" name="order" class="form-control" id="order" value="{{ old('order', $project->order) }}" />
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($project->status == $status->value)>
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
    </script>
    @endpush
</x-app-layout>