@extends('web.layout')

@section('title', 'Đăng ký tài khoản khách hàng')
@section('description', 'Đăng ký tài khoản miễn phí để theo dõi đơn hàng đã đặt và đang đặt tại Sàn Điện Máy.')

@section('content')
    <div class="breadcrumbs" style="margin-bottom: 30px">
        <div class="container">
            <div class="breadcrumbs-main d-flex gap10">
                <a class="breadcrumbs-home d-flex gap05" href="{{ route('frontend.page.home') }}">
                    Trang chủ
                </a>
                <div class="breadcrumbs-icon">
                    <img src="images/ic-right.svg" width="8" height="14" alt="Mũi tên phân cách breadcrumb">
                </div>
                <span class="breadcrumbs-link">Đăng ký</span>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <div class="cust-auth cust-card">
            <h1 class="cust-h">Đăng ký</h1>
            <p class="cust-sub">Dùng email bạn đã đặt hàng, hệ thống sẽ gắn các đơn cũ vào tài khoản này.</p>

            <form method="POST" action="{{ route('frontend.customer.register.store') }}" class="cust-form"
                data-skip-ajax>
                @csrf
                <div class="cust-field">
                    <label for="register-name">Họ và tên</label>
                    <input type="text" id="register-name" name="name" value="{{ old('name') }}" required
                        autocomplete="name">
                    @error('name')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="cust-field">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" value="{{ old('email') }}" required
                        autocomplete="email">
                    @error('email')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="cust-field">
                    <label for="register-phone">Số điện thoại</label>
                    <input type="tel" id="register-phone" name="phone" value="{{ old('phone') }}" required
                        inputmode="numeric" maxlength="10" autocomplete="tel">
                    @error('phone')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="cust-field">
                    <label for="register-password">Mật khẩu</label>
                    <input type="password" id="register-password" name="password" required minlength="8"
                        autocomplete="new-password">
                    @error('password')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="cust-field">
                    <label for="register-password-confirm">Nhập lại mật khẩu</label>
                    <input type="password" id="register-password-confirm" name="password_confirmation" required
                        minlength="8" autocomplete="new-password">
                </div>
                <div class="cust-actions">
                    <button type="submit" class="cust-btn">Tạo tài khoản</button>
                </div>
                <p class="cust-links">Đã có tài khoản? <a href="{{ route('frontend.customer.login') }}">Đăng nhập</a></p>
            </form>
        </div>
    </div>
@stop

@push('scripts-header')
    <meta name="robots" content="noindex, follow">
@endpush

@push('page-css')
    <link rel="stylesheet"
        href="{{ asset('css-new/dist/customer.min.css?v=' . filemtime(public_path('css-new/dist/customer.min.css'))) }}">
@endpush
