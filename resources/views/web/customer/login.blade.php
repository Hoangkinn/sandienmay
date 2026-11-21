@extends('web.layout')

@section('title', 'Đăng nhập tài khoản khách hàng')
@section('description', 'Đăng nhập tài khoản để theo dõi đơn hàng đã đặt và đang đặt tại Sàn Điện Máy.')

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
                <span class="breadcrumbs-link">Đăng nhập</span>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <div class="cust-auth cust-card">
            <h1 class="cust-h">Đăng nhập</h1>
            <p class="cust-sub">Theo dõi đơn hàng đang đặt và đã đặt, sửa thông tin giao hàng của bạn.</p>

            {{-- data-skip-ajax: main.js:190 chặn submit mọi form frontend để gửi AJAX, làm lỗi đăng nhập
                 không được render lại và không redirect được. Form auth phải nộp thật. --}}
            <form method="POST" action="{{ route('frontend.customer.login.store') }}" class="cust-form"
                data-skip-ajax>
                @csrf
                <div class="cust-field">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" value="{{ old('email') }}" required
                        autocomplete="email">
                    @error('email')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="cust-field">
                    <label for="login-password">Mật khẩu</label>
                    <input type="password" id="login-password" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="cust-error">{{ $message }}</span>
                    @enderror
                </div>
                <label class="cust-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Ghi nhớ đăng nhập
                </label>
                <div class="cust-actions">
                    <button type="submit" class="cust-btn">Đăng nhập</button>
                </div>
                <p class="cust-links">Chưa có tài khoản? <a href="{{ route('frontend.customer.register') }}">Đăng ký</a></p>
            </form>
        </div>
    </div>
@stop

@push('scripts-header')
    {{-- Trang chỉ dành cho người đã đặt hàng: giữ không index, nhưng vẫn theo link tới đơn hàng --}}
    <meta name="robots" content="noindex, follow">
@endpush

@push('page-css')
    <link rel="stylesheet"
        href="{{ asset('css-new/dist/customer.min.css?v=' . filemtime(public_path('css-new/dist/customer.min.css'))) }}">
@endpush
