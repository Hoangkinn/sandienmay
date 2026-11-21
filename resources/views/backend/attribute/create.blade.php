<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo mới thuộc tính') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.attribute.index') }}">{{ __('Danh sách thuộc tính') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo mới thuộc tính') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.attribute.store') }}" enctype="multipart/form-data">
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
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{ __('Đường dẫn') }} <code>*</code></label>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-control @error('slug') is-invalid @enderror">
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
                                <div class="form-group">
                                    <label for="title">{{ __('Danh mục sử dụng') }}</label>
                                    <select class="form-control select2" multiple="multiple" name="categories_id[]">
                                        <option value="">None</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ in_array($category->id, old('categories_id', []) ?? []) ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <p>Chọn danh mục sử dụng thuộc tính này, mặc định hiển thị ở tất cả danh mục.</p>
                                </div>
                                <!-- <div class="form-group">
                                    <label for="title">{{ __('Tùy chọn') }}</label>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="is_search" type="checkbox" id="is_search" @checked(old('is_search')) />
                                        <label for="is_search" class="custom-control-label">Dùng lọc Sản phẩm ở danh mục</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="is_display" type="checkbox" id="is_display" @checked(old('is_display')) />
                                        <label for="is_display" class="custom-control-label">Hiển thị ở bảng thông số kỹ thuật</label>
                                    </div>
                                </div> -->
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
                                    <input type="number" name="position" value="{{ old('position', 0) }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
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
        });
    </script>
    @endpush
</x-app-layout>