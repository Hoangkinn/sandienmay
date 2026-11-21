<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Admin | Dashboard') }}</title>
    <x-setting fieldKey="favicon"></x-setting>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backend/nestable.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backend/toastr.min.css') }}">
    @stack('backend-css')
    <link rel="stylesheet" href="{{ asset('css/backend/customize.css') }}?v={{ filemtime(public_path('css/backend/customize.css')) }}">
</head>
<style>
.nav-item.dropdown > .dropdown-menu {
    display: block !important; 
    visibility: hidden;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.25s ease-in-out;
    pointer-events: none; 
    position: absolute;
    top: 100%;
    left: 0;
}

.nav-item.dropdown:hover > .dropdown-menu {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
</style>

<body class="sidebar-mini layout-navbar-fixed layout-top-nav">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand-md navbar-light navbar-dark">
            <div class="container-fluid">
                <a href="{{ route('frontend.page.home') }}" class="navbar-brand">
                    <img class="brand-image img-circle elevation-3" src="{{ asset('images/logo.svg') }}" alt="AdminLTELogo">
                </a>
                <div class="collapse navbar-collapse order-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="{{ route('backend.dashboard') }}" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                {{ __('Home') }}
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
                                <i class="nav-icon fas fa-paper-plane"></i>
                                {{ __('Nội dung') }}
                            </a>
                            <ul class="dropdown-menu border-0 shadow">
                                <li class="nav-item">
                                    <a href="{{ route('backend.category.index', ['type' => App\Enums\CategoryType::POST->value]) }}" class="dropdown-item">
                                        {{ __('Chuyên mục') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.post.index') }}" class="dropdown-item">
                                        {{ __('Bài viết') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.page.index') }}" class="dropdown-item">
                                        {{ __('Nội dung cố định') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.video.index') }}" class="dropdown-item">
                                        {{ __('Video') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.review.index') }}" class="dropdown-item">
                                        Đánh giá (trang chủ)
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" id="san-pham" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  class="nav-link  dropdown-toggle">
                                <i class="nav-icon fab fa-product-hunt"></i>
                                {{ __('Sản phẩm') }}
                            </a>
                            <ul aria-labelledby="san-pham" class="dropdown-menu border-0 shadow">
                                <li class="nav-item">
                                    <a href="{{ route('backend.category.index', ['type' => App\Enums\CategoryType::PRODUCT->value]) }}" class="dropdown-item">
                                        {{ __('Danh mục') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.product.index') }}" class="dropdown-item">
                                        {{ __('Danh sách sản phẩm') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.brand.index') }}" class="dropdown-item">
                                        {{ __('Thương hiệu') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.attribute.index') }}" class="dropdown-item">
                                        {{ __('Thuộc tính sản phẩm') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.comment.index') }}" class="dropdown-item">
                                        {{ __('Bình luận sản phẩm') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.contact.index') }}" class="dropdown-item">
                                        {{ __('Thông tin liên hệ khách hàng') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.email-subscriber.index') }}" class="dropdown-item">
                                        {{ __('Email đăng ký nhận tin') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" id="don-hang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link  dropdown-toggle">
                                <i class="nav-icon fas fa-cart-arrow-down"></i>
                                {{ __('Đơn hàng') }}
                            </a>
                            <ul aria-labelledby="don-hang" class="dropdown-menu border-0 shadow">
                                <li class="nav-item">
                                    <a href="{{ route('backend.order.index') }}" class="dropdown-item">
                                        {{ __('Danh sách hóa đơn') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.customer.index') }}" class="dropdown-item">
                                        {{ __('Danh sách khách hàng') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" id="marketing" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link  dropdown-toggle">
                                <i class="nav-icon fas fa-bullhorn"></i>
                                {{ __('Marketing') }}
                            </a>
                            <ul aria-labelledby="marketing" class="dropdown-menu border-0 shadow">
                                <li class="nav-item">
                                    <a href="{{ route('backend.banner.index') }}" class="dropdown-item">
                                        {{ __('Danh sách banner') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" id="he-thong" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
                                <i class="nav-icon fas fa-wrench"></i>
                                {{ __('Hệ thống') }}
                            </a>
                            <ul aria-labelledby="he-thong" class="dropdown-menu border-0 shadow">
                                <li class="nav-item">
                                    <a href="{{ route('backend.setting.index') }}" class="dropdown-item">
                                        {{ __('Thông tin website') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.user.index') }}" class="dropdown-item">
                                        {{ __('Danh sách quản trị') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.redirect.index') }}" class="dropdown-item">
                                        {{ __('Url Redirect') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('backend.setting-field.index') }}" class="dropdown-item">
                                        {{ __('Cấu hình') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('backend.history.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                {{ __('Lịch sử') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                    <li class="nav-item dropdown">
                        <a href="#" id="he-thong" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
                            {{ Auth::user()->name }}
                        </a>
                        <ul aria-labelledby="he-thong" class="dropdown-menu border-0 shadow">
                            <li class="nav-item">
                                <a href="{{ route('backend.admin.change-pass') }}" class="dropdown-item">Thay đổi mật khẩu</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link class="dropdown-item" :href="route('logout')" onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                        <i class="fas fa-sign-out-alt"></i>
                                        {{ __('Đăng xuất') }}
                                    </x-dropdown-link>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="content-wrapper">
            {{ $slot }}
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2026 Sandienmay.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.0
            </div>
        </footer>
    </div>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/sparkline.js') }}"></script>
    <script src="{{ asset('js/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.js') }}"></script>
    <script src="{{ asset('js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-switch.min.js') }}"></script>
    <script src="{{ asset('js/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    <script src="{{ asset('js/backend/toastr.min.js') }}"></script>
    <script src="{{ asset('js/backend/tinymce/tinymce.min.js') }}" defer></script>
    <script src="{{ asset('js/backend/tinymce/tinymce.init.js') }}" defer></script>
    <script src="{{ asset('js/backend/jquery.nestable.js') }}"></script>
	<script src="{{ asset('js/backend/format-price.js') }}"></script>
    <script src="{{ asset('js/backend/customize.js') }}"></script>
    <script>
        $(function() {
            var msgSuccess = "{{ session('backend.toast.success') }}";
            if (msgSuccess) {
                toastr.success(msgSuccess);
            }

            var msgInfo = "{{ session('backend.toast.info') }}";
            if (msgInfo) {
                toastr.info(msgInfo);
            }

            var msgError = "{{ session('backend.toast.error') }}";
            if (msgError) {
                toastr.error(msgError);
            }

            var msgWarning = "{{ session('backend.toast.warning') }}";
            if (msgWarning) {
                toastr.warning(msgWarning);
            }
        });
    </script>
    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-save-exit]');
            if (!button) return;

            const form = button.form;
            if (!form || !form.reportValidity()) return;

            event.preventDefault();
            if (button.dataset.saving === 'true') return;

            const slug = form.querySelector('[name="slug"]')?.value.trim();
            const frontendUrl = slug && button.dataset.frontendTemplate
                ? button.dataset.frontendTemplate.replace('__SLUG__', encodeURIComponent(slug))
                : null;
            const frontendTab = window.open(frontendUrl || window.location.href, '_blank');
            if (!frontendTab) {
                toastr.error('Trình duyệt đang chặn tab mới. Hãy cho phép pop-up rồi thử lại.');
                return;
            }

            button.dataset.saving = 'true';
            button.disabled = true;
            if (window.tinymce) tinymce.triggerSave();

            const formData = new FormData(form);
            formData.set(button.name, button.value);

            try {
                const response = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (!response.ok || !result.redirect_url) {
                    throw new Error(result.message || 'Không thể lưu dữ liệu.');
                }

                frontendTab.location.replace(result.redirect_url);
                toastr.success('Đã lưu dữ liệu.');
            } catch (error) {
                frontendTab.close();
                toastr.error(error.message || 'Lưu không thành công.');
            } finally {
                button.disabled = false;
                delete button.dataset.saving;
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
