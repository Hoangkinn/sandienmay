<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Cập nhật khuyến mại') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.promotion.index') }}">{{ __('Danh sách khuyến mại') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Cập nhật khuyến mại') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.promotion.update', ['id' => $promotion->id]) }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin khuyến mại') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">{{ __('Tiêu đề') }} <code>*</code></label>
                                    <input type="text" name="title" value="{{ old('title', $promotion->title) }}" class="form-control @error('title') is-invalid @enderror" id="title" placeholder="Tiêu đề">
                                    @error('title')
                                    <span id="title-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="category_ids">{{ __('Danh mục') }}</label>
                                    <select name="category_ids[]" class="form-control select2" multiple>
                                        <option value="">None</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $category->isSelected($category->id, $promotion->id) }}>
                                                {{ $category->name }}
                                            </option>
                                            @if(count($category->childs))
                                                @include('backend.category.child.promotion', [
                                                    'childs' => $category->childs,
                                                    'promotion_id' => $promotion->id,
                                                    'expandable' => '--'
                                                ])
                                            @endif
                                        @endforeach
                                    </select>
                                    <small>Áp dụng cho sản phẩm theo danh mục.</small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" value="1" name="apply_for_child" type="checkbox" id="apply_for_child" @checked(old('apply_for_child', $promotion->apply_for_child)) />
                                        <label for="apply_for_child" class="custom-control-label">Áp dụng cho danh mục con</label>
                                    </div>
                                    <small>Các danh mục con sẽ được áp dụng KM này.</small>
                                </div>
                                <div class="form-group">
                                    <label for="product_ids">{{ __('Sản phẩm') }}</label>
                                    <select name="product_ids[]" class="form-control select2" multiple>
                                        <option value="">None</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ $product->isSelected($product->id, $promotion->id) }}>
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small>Áp dụng cho từng sản phẩm.</small>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="from_date">{{ __('Từ ngày') }} <code>*</code></label>
                                            <div class="input-group date" id="from_date" data-target-input="nearest">
                                                <input type="text" name="from_date" class="form-control datetimepicker-input @error('from_date') is-invalid @enderror" value="{{ old('from_date', $promotion->from_date) }}" data-target="#from_date" placeholder="dd/mm/yyyy" />
                                                <div class="input-group-append" data-target="#from_date" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                                @error('from_date')
                                                <span id="from_date-error" class="error invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="to_date">{{ __('Đến ngày') }} <code>*</code></label>
                                            <div class="input-group date" id="to_date" data-target-input="nearest">
                                                <input type="text" name="to_date" class="form-control datetimepicker-input @error('to_date') is-invalid @enderror" value="{{ old('to_date', $promotion->to_date) }}" data-target="#to_date" placeholder="dd/mm/yyyy" />
                                                <div class="input-group-append" data-target="#to_date" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                                @error('to_date')
                                                <span id="to_date-error" class="error invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password">{{ __('Nội dung') }} <code>*</code></label>
                                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" placeholder="Nội dung">{{ old('content', $promotion->content) }}</textarea>
                                    @error('content')
                                    <span id="content-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.promotion.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                <a href="{{ route('backend.promotion.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($status->value == $promotion->status)>
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
            $('#from_date').datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $('#to_date').datetimepicker({
                format: 'YYYY-MM-DD'
            });

            tinymceInit.selector = '#content';
            tinymceInit.file_picker_callback = function(cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };
            tinymce.init(tinymceInit);
        });
    </script>
    @endpush
</x-app-layout>