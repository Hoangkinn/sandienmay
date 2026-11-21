<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Thông tin website') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Hệ thống') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.setting.save') }}">
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
                                    <label for="name">{{ __('Tên gọi website') }} <code>*</code></label>
                                    <input type="text" name="site_name" value="{{ old('site_name', $settings[1]['setting_value']) }}" class="form-control @error('site_name') is-invalid @enderror" placeholder="Tên gọi website">
                                    @error('site_name')
                                    <span id="site_name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="site_tite">{{ __('Thẻ Meta Title') }} <code>*</code></label>
                                    <input type="text" name="site_tite" value="{{ old('site_tite', $settings[2]['setting_value']) }}" class="form-control @error('site_tite') is-invalid @enderror" placeholder="Thẻ Meta Title">
                                    @error('site_meta_title')
                                    <span id="site_tite-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="site_keywords">{{ __('Thẻ Meta Keywords') }} <code>*</code></label>
                                    <input type="text" name="site_keywords" value="{{ old('site_keywords', $settings[3]['setting_value']) }}" class="form-control @error('site_keywords') is-invalid @enderror" placeholder="Thẻ Meta Keywords">
                                    @error('site_keywords')
                                    <span id="site_keywords-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="site_description">{{ __('Thẻ Meta Description') }} <code>*</code></label>
                                    <textarea name="site_description" class="form-control @error('site_description') is-invalid @enderror" rows="3" placeholder="Thẻ Meta Description">{{ old('site_description', $settings[4]['setting_value']) }}</textarea>
                                    @error('site_description')
                                    <span id="site_description-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="homepage_schema">{{ __('Schema Trang chủ (JSON-LD)') }}</label>
                                    <textarea name="homepage_schema" class="form-control @error('homepage_schema') is-invalid @enderror" rows="6" placeholder='Nhập mã Schema JSON-LD cho trang chủ'>{{ old('homepage_schema', $homepage_schema ?? '') }}</textarea>
                                    @error('homepage_schema')
                                    <span id="homepage_schema-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted mt-1 d-block">Nếu để trống, hệ thống sẽ tự động dùng 3 Schema mặc định (Organization, WebSite, LocalBusiness).</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-right">{{ __('Lưu') }}</button>
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