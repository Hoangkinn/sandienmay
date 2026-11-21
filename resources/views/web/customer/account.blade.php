@extends('web.layout')

@section('title', 'Tài khoản của tôi')
@section('description', 'Quản lý thông tin và đơn hàng của bạn tại Sàn Điện Máy.')

@php $activeNav = 'account'; @endphp

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
                <span class="breadcrumbs-link">Tài khoản của tôi</span>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <div class="cust-layout">
            <aside>@include('web.customer._nav')</aside>

            <div class="cust-main">
                @if (session('success'))
                    <div class="cust-alert">{{ session('success') }}</div>
                @endif

                <div class="cust-card">
                    <h1 class="cust-h">Xin chào, {{ $customer->name }}</h1>
                    <p class="cust-sub">
                        Email đăng nhập: {{ $customer->email }} —
                        <a href="{{ route('frontend.customer.orders') }}">{{ $orderCount }} đơn hàng</a>.
                    </p>
                </div>

                <div class="cust-card">
                    <h2 class="cust-h2">Thông tin của tôi</h2>
                    <form method="POST" action="{{ route('frontend.customer.profile') }}" data-skip-ajax>
                        @csrf
                        <div class="cust-field">
                            <label for="account-name">Họ và tên</label>
                            <input type="text" id="account-name" name="name"
                                value="{{ old('name', $customer->name) }}" required autocomplete="name">
                            @error('name')
                                <span class="cust-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="cust-field">
                            <label for="account-phone">Số điện thoại</label>
                            <input type="tel" id="account-phone" name="phone"
                                value="{{ old('phone', $customer->phone_number) }}" required inputmode="numeric"
                                maxlength="10" autocomplete="tel">
                            @error('phone')
                                <span class="cust-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="cust-field">
                            <label for="account-address">Địa chỉ nhận hàng</label>
                            <input type="text" id="account-address" name="address"
                                value="{{ old('address', $customer->address) }}" autocomplete="street-address">
                            @error('address')
                                <span class="cust-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="cust-actions">
                            <button type="submit" class="cust-btn">Lưu thông tin</button>
                        </div>
                    </form>
                </div>

                <div class="cust-card">
                    <h2 class="cust-h2">Đổi mật khẩu</h2>
                    <form method="POST" action="{{ route('frontend.customer.password') }}" data-skip-ajax>
                        @csrf
                        <div class="cust-field">
                            <label for="current-password">Mật khẩu hiện tại</label>
                            <input type="password" id="current-password" name="current_password" required
                                autocomplete="current-password">
                            @error('current_password')
                                <span class="cust-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="cust-field">
                            <label for="new-password">Mật khẩu mới</label>
                            <input type="password" id="new-password" name="password" required minlength="8"
                                autocomplete="new-password">
                            @error('password')
                                <span class="cust-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="cust-field">
                            <label for="new-password-confirm">Nhập lại mật khẩu mới</label>
                            <input type="password" id="new-password-confirm" name="password_confirmation" required
                                minlength="8" autocomplete="new-password">
                        </div>
                        <div class="cust-actions">
                            <button type="submit" class="cust-btn is-ghost">Đổi mật khẩu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts-header')
    <meta name="robots" content="noindex, nofollow">
@endpush

@push('page-css')
    <link rel="stylesheet"
        href="{{ asset('css-new/dist/customer.min.css?v=' . filemtime(public_path('css-new/dist/customer.min.css'))) }}">
@endpush
