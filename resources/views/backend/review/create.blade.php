<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tạo mới đánh giá khách hàng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.review.index') }}">{{ __('Danh sách đánh giá') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo mới') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.review.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin đánh giá') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="avatar">{{ __('Ảnh đại diện') }} <code>*</code></label><br>
                                    <i class="d-block">Kích thước chuẩn là 75 x 75px</i>
                                    <input type="file" name="avatar" id="avatar" class="@error('avatar') is-invalid @enderror">
                                    @error('avatar')
                                    <span id="avatar-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="name">{{ __('Tên người đánh giá') }}</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" id="name">
                                </div>
                                <div class="form-group">
                                    <label for="content">{{ __('Nội dung đánh giá') }}</label>
                                    <textarea id="content" name="content" class="form-control" rows="4">{{ old('content') }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.review.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                    </div>
                </div>
            </div>
        </form>
    </section>
</x-app-layout>