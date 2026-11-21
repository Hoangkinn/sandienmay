<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Thay đổi mật khẩu') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('Thay đổi mật khẩu') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    @if (session('backend.toast.error'))
    <div class="alert alert-danger" role="alert">
        {{ session('backend.toast.error') }}
    </div>
    @endif

    @if (session('backend.toast.success'))
    <div class="alert alert-success" role="alert">
        {{ session('backend.toast.success') }}
    </div>
    @endif

    <section class="content">
        <form method="POST" action="{{ route('backend.admin.update-pass') }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="password">{{ __('Mật khẩu') }} <code>*</code></label>
                                    <input type="password" name="password" id="password" value="{{ old('password') }}" placeholder="Mật khẩu" class="form-control @error('password') is-invalid @enderror" />
                                    @error('password')
                                    <span id="password-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">{{ __('Nhập lại mật khẩu') }} <code>*</code></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="Nhập lại mật khẩu" class="form-control @error('password_confirmation') is-invalid @enderror" />
                                    @error('password_confirmation')
                                    <span id="password_confirmation-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button>
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
                                <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button>
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

</x-app-layout>