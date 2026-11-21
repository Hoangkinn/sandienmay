<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo Url Redirect') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.redirect.index') }}">{{ __('Url Redirect') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Tạo Url Redirect') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.redirect.store') }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin Url Redirect') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="redirect_from">{{ __('Từ') }} <code>*</code></label>
                                    <input type="text" name="redirect_from" value="{{ old('redirect_from') }}" class="form-control @error('redirect_from') is-invalid @enderror" id="redirect_from" placeholder="{{ url()->current() }}">
                                    @error('redirect_from')
                                    <span id="redirect_from-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="redirect_to">{{ __('Đến') }} <code>*</code></label>
                                    <input type="text" name="redirect_to" value="{{ old('redirect_to') }}" class="form-control @error('redirect_to') is-invalid @enderror" id="redirect_to" placeholder="{{ url()->current() }}">
                                    @error('redirect_to')
                                    <span id="redirect_to-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2 @error('redirect_code') is-invalid @enderror" name="redirect_code">
                                        @foreach ($statusCode as $code)
                                        <option value="{{ $code->value }}">{{ $code->value }}</option>
                                        @endforeach
                                    </select>
                                    @error('redirect_code')
                                    <span id="redirect_code-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.redirect.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
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
                                <a href="{{ route('backend.redirect.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
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