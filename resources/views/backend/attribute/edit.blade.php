<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Cập nhật thuộc tính') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.attribute.index') }}">{{ __('Danh sách thuộc tính') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Cập nhật') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.attribute.update', ['id' => $attribute->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin thuộc tính') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">{{ __('Tên') }} <code>*</code></label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $attribute->name) }}" class="form-control @error('name') is-invalid @enderror">
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" value="{{ old('slug', $attribute->slug) }}" class="form-control @error('slug') is-invalid @enderror">
                                    @error('slug')
                                    <span id="slug-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- <div class="form-group">
                                    <label for="parent_id">{{ __('Danh mục cha') }}</label>
                                    <select class="form-control select2" name="parent_id" id="parent_id">
                                        <option value="">None</option>
                                    </select>
                                </div> -->
                                <!-- <div class="form-group">
                                    <label for="image">{{ __('Hình ảnh') }}</label><br>
                                    <input type="file" name="image" id="image" value="" class="">
                                </div> -->
                                @php
                                    $selectedCategories = old(
                                        'categories_id',
                                        $attribute->categories->pluck('id')->toArray()
                                    );
                                @endphp
                                <div class="form-group">
                                    <label for="title">{{ __('Danh mục sử dụng') }}</label>
                                    <select class="form-control select2" multiple="multiple" name="categories_id[]">
                                        <option value="">None</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"  {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <p>Chọn danh mục sử dụng thuộc tính này, mặc định hiển thị ở tất cả danh mục.</p>
                                </div>
                                <!-- <div class="form-group">
                                    <label for="title">{{ __('Tùy chọn') }}</label>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="is_search" type="checkbox" id="is_search" @checked(old('is_search', $attribute->is_search)) />
                                        <label for="is_search" class="custom-control-label">Dùng lọc Sản phẩm ở danh mục</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="is_display" type="checkbox" id="is_display" @checked(old('is_display', $attribute->is_display)) />
                                        <label for="is_display" class="custom-control-label">Hiển thị ở bảng thông số kỹ thuật</label>
                                    </div>
                                </div> -->
                                <div class="form-group list_children" data-route="{{ route('backend.option.save', ['attribute_id' => $attribute->id]) }}">
                                    <label for="title">{{ __('Quản lý các giá trị') }}</label>
                                    <table class="table table-sm table-bordered w-auto">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tên</th>
                                                <th>Thứ tự</th>
                                                <th>Trạng thái</th>
                                                <th>Tác vụ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attribute->options as $option)
                                            <tr data-_token="{{ csrf_token() }}" data-option_id="{{ $option->id }}" data-position="{{ $option->position }}" data-name="{{ $option->name }}" data-status="{{ $option->status }}">
                                                <td style="vertical-align: middle;">{{ $option->id }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm edit-input" data-field="name" value="{{ old('name', $option->name) }}" disabled="">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm edit-input" data-field="position" value="{{ old('position', $option->position) }}" disabled="">
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm edit-input" data-field="status" disabled="">
                                                        @foreach ($statusList as $status)
                                                        <option value="{{ $status->value }}" @selected($option->status == $status->value)>
                                                            {{ $status->label() }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-default btn-disable edit-table-btn-edit">Sửa</button>
                                                    <button type="button" class="btn btn-sm btn-primary btn-enable edit-table-btn-save" style="display: none;">Sửa</button>
                                                    <button type="button" class="btn btn-sm btn-default btn-enable edit-table-btn-cancel" style="display: none;">Thôi</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr data-_token="{{ csrf_token() }}" data-status="actived" data-name="" data-position="" id="add-option">
                                                <td style="vertical-align: middle;">0</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm edit-input edit-input-name" data-field="name" value="" disabled="">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm edit-input edit-input-position" data-field="position" value="0" disabled="">
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm edit-input edit-input-status" data-field="status" disabled="">
                                                        @foreach ($statusList as $status)
                                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-default btn-disable edit-table-btn-edit">Thêm mới </button>
                                                    <button type="button" class="btn btn-sm btn-primary btn-enable edit-table-btn-save" style="display: none;">Thêm mới </button>
                                                    <button type="button" class="btn btn-sm btn-default btn-enable edit-table-btn-cancel" style="display: none;">Thôi</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.attribute.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                <a href="{{ route('backend.attribute.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                    <label for="position">{{ __('Thứ tự') }}</label>
                                    <input type="number" name="position" value="{{ old('position', $attribute->position) }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($attribute->status == $status->value)>
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

            let vm = this,
                wrapper = $('.list_children'),
                route = wrapper.data('route');

            wrapper.on('click', '.edit-table-btn-edit', function() {
                let tr = $(this).closest('tr'),
                    input_disabled = tr.find('.edit-input[disabled]');

                input_disabled.prop('disabled', false);
                tr.find('.btn-enable').show();
                tr.find('.btn-disable').hide();
            });
            wrapper.on('click', '.edit-table-btn-save', function() {
                let tr = $(this).closest('tr'),
                    btn = $(this);
                btn.prop('disabled', true);
                $.post(route, tr.data(), function(response) {
                    if ('message' in response) {
                        if (response.error) {
                            toastr.error(response.message);
                        } else {
                            toastr.success(response.message);
                            loadAttribute();
                        }
                    }
                }).always(function() {
                    tr.find('.edit-input').prop('disabled', true);
                    tr.find('.btn-enable').hide();
                    tr.find('.btn-disable').show();
                });
            });
            wrapper.on('click', '.edit-table-btn-cancel', function() {
                let tr = $(this).closest('tr');
                tr.find('.edit-input').prop('disabled', true);
                tr.find('.btn-enable').hide();
                tr.find('.btn-disable').show();
            });
            wrapper.on('change', '[data-field]', function() {
                let tr = $(this).closest('tr'),
                    field = $(this).data('field');
                tr.attr('data-' + field, $(this).val());
            });

            function loadAttribute() {
                let wrapper = $('.list_children tbody'),
                    route = "{{ route('backend.option.list', ['attribute_id' => $attribute->id]) }}";
                $.get(route, function(response) {
                    if ('view' in response) {
                        wrapper.html(response.view);
                    }
                });
            }

        });
    </script>
    @endpush
</x-app-layout>