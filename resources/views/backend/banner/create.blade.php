<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo mới banner') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.banner.index') }}">{{ __('Danh sách banner') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo mới') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.banner.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin banner') }}</h3> 
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="image">{{ __('Hình ảnh Destop') }} <code>*</code></label><br>
                                    <input type="file" name="image" id="image" class="@error('image') is-invalid @enderror">
                                    @error('image')
                                    <span id="image-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="image_mobile">{{ __('Hình ảnh Mobile') }}</label><br>
                                    <input type="file" name="image_mobile" id="image_mobile" class="@error('image_mobile') is-invalid @enderror">
                                    @error('image_mobile')
                                    <span id="image_mobile-error" class="error invalid-feedback" style="display: block;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Tiêu đề') }}</label>
                                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" id="title">
                                </div>
                                <div class="form-group">
                                    <label for="link">{{ __('Link') }}</label>
                                    <input type="text" name="link" value="{{ old('link') }}" class="form-control" id="link" placeholder="http:://demo.com">
                                </div>
                                <div class="form-group">
                                    <label for="position">{{ __('Vị trí') }}</label>
                                    <select class="form-control select2" name="position">
                                        <option value="0" {{ old('position') == 0 ? 'selected="selected"' : '' }}>Top</option>
                                        <option value="1" {{ old('position') == 1 ? 'selected="selected"' : '' }}>Midde</option>
                                        <option value="2" {{ old('position') == 2 ? 'selected="selected"' : '' }}>Footer</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="target">{{ __('Target') }}</label>
                                    <select class="form-control select2" name="target">
                                        @foreach ($targetList as $target)
                                        <option value="{{ $target->value }}">{{ $target->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.banner.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                <a href="{{ route('backend.banner.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                    <label for="title">{{ __('Trạng thái') }}</label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Số thứ tự (Order) -->
                                <div class="form-group">
                                    <label for="order">{{ __('Số thứ tự') }}</label>
                                    <input type="number" 
                                        class="form-control @error('order') is-invalid @enderror" 
                                        id="order" 
                                        name="order" 
                                        value="{{ old('order', $banner->order ?? 0) }}" 
                                        placeholder="Nhập số thứ tự...">
                                    @error('order')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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
        });
    </script>
    @endpush
</x-app-layout>