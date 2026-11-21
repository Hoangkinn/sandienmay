<header class="site-header">
    @php $isHome = request()->is('/'); @endphp

    {{-- Marquee cần 3 bản sao: điều kiện loop liền mạch là (n-1) * bề rộng 1 bản > bề rộng
         .topbar-ticker; 2 bản (844px) không đủ ở 1440px (ticker ~1067px) -> hở nền. --}}
    @php
        $commitItems = [
            ['icon' => 'icon1.svg', 'w' => 20, 'h' => 20, 'text' => '100% hàng chính hãng'],
            ['icon' => 'icon2.svg', 'w' => 20, 'h' => 16, 'text' => 'Miễn phí giao hàng nội thành HN&HCM'],
            ['icon' => 'icon3.svg', 'w' => 24, 'h' => 20, 'text' => 'Giao hàng toàn quốc'],
            ['icon' => 'icon4.svg', 'w' => 24, 'h' => 20, 'text' => '7 ngày đổi trả'],
        ];
    @endphp

    <div class="topbar">
        <div class="container">
            <span class="commit-label">Cam kết</span>

            <div class="topbar-ticker">
                <div class="topbar-track">
                    @for ($lap = 0; $lap < 3; $lap++)
                        <ul class="commit-list" @if ($lap > 0) aria-hidden="true" @endif>
                            @foreach ($commitItems as $item)
                                <li>
                                    <img src="{{ asset('images/' . $item['icon']) }}" alt="{{ $item['text'] }}" width="{{ $item['w'] }}" height="{{ $item['h'] }}" loading="lazy">{{ $item['text'] }}
                                </li>
                            @endforeach
                        </ul>
                    @endfor
                </div>
            </div>

            <nav class="topbar-actions" aria-label="Tài khoản và tư vấn">
                @auth('customers')
                    @php $customerTopbar = auth('customers')->user(); @endphp
                    <a href="{{ route('frontend.customer.account') }}" class="topbar-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ $customerTopbar->name }}</span>
                    </a>
                    <span class="topbar-sep" aria-hidden="true"></span>
                    <a href="{{ route('frontend.customer.orders') }}" class="topbar-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                        <span>Đơn hàng</span>
                    </a>
                    <span class="topbar-sep" aria-hidden="true"></span>
                    {{-- data-skip-ajax: form này phải nộp thật để redirect, xem ghi chú ở trang đăng nhập --}}
                    <form method="POST" action="{{ route('frontend.customer.logout') }}" class="topbar-logout"
                        data-skip-ajax>
                        @csrf
                        <button type="submit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('frontend.customer.register') }}" class="topbar-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Đăng ký</span>
                    </a>
                    <span class="topbar-sep" aria-hidden="true"></span>
                    <a href="{{ route('frontend.customer.login') }}" class="topbar-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Đăng nhập</span>
                    </a>
                @endauth

                <span class="topbar-sep" aria-hidden="true"></span>

                <a href="{{ $linkZalo }}" class="topbar-link topbar-zalo" target="_blank" rel="noopener nofollow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.6 8.6 0 0 1-3.8-.9L3 21l2.1-5.5a8.1 8.1 0 0 1-1.6-4.8A8.4 8.4 0 0 1 12 2.4a8.4 8.4 0 0 1 9 8.3v.8z"/></svg>
                    <span>Zalo tư vấn</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="header-top">
        <div class="container">
            <{{ $isHome ? 'h1' : 'div' }} class="logo">
                <a href="{{ url('/') }}" aria-label="Trang chủ Sàn điện máy" class="d-flex">
                    <img src="{{ asset('images/logo-m-v2.png') }}" alt="Sàn điện máy" width="112" height="79" loading="eager" fetchpriority="high">
                </a>
            </{{ $isHome ? 'h1' : 'div' }}>

            <button type="button" class="cate-btn" id="mega-toggle" aria-expanded="false" aria-controls="megaMenu">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                    <path d="M2 2h5v5H2zM11 2h5v5h-5zM2 11h5v5H2zM11 11h5v5h-5z" fill="currentColor"/>
                </svg>
                <span>Danh mục</span>
                <i class="cate-btn-arrow" aria-hidden="true"></i>
            </button>

            <search class="search-box">
                <form action="{{ \App\Helpers\GetFeUrlHelper::search() }}" class="formSearch position-relative" data-skip-ajax>
                    <input type="search" name="s" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm sản phẩm" required>
                    <button type="submit" aria-label="Nút tìm kiếm">
                        <img src="{{ asset('images/search.png') }}" alt="tìm kiếm" width="20" height="20" loading="eager">
                    </button>
                    <div class="bg-fff searchResult position-absolute top-100 start-0 col-12 mh-350 overflow-auto px-10 z-3"></div>
                </form>
            </search>

            <div class="header-actions">
                {{-- mobile <span> display:none nen phai giu aria-label; aria-label = nguyen van text nhin thay de khong loi label-content-name-mismatch --}}
                <a href="tel:{!! $phoneHn !!}" class="hotline" aria-label="Hotline {{ $phoneHn }}">
                    <img src="{{ asset('images/phone-call-v2.svg') }}" alt="Gọi điện tư vấn" width="28" height="28" loading="eager">
                    <span>Hotline <br> <b>{!! $phoneHn !!}</b></span>
                </a>

                @php $cartCount = collect(session('carts'))->sum('quantity'); @endphp
                <a href="{{ route('frontend.cart.index') }}" class="cart-link" aria-label="Giỏ hàng {{ $cartCount }} sản phẩm">
                    <img src="{{ asset('images/shopping-cart.svg') }}" alt="Giỏ hàng" width="24" height="24" loading="eager">
                    <span>Giỏ hàng</span>
                    <span class="jsCountCart text-fff">{{ $cartCount }}</span>
                </a>

                <button class="menu-link" id="menu-toggle" aria-label="Menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mega menu desktop: ẩn hoàn toàn dưới 993px (mobile dùng drawer #modelMenuMobi) --}}
    <div class="mega" id="megaMenu">
        <div class="container">
            <x-fe.menu mode="mega"></x-fe.menu>
        </div>
    </div>

    {{-- <div class="commit-bar">
        <div class="container">
            <ul class="commit-list">
                <li class="commit-label">Cam kết</li>
                <li><img src="{{ asset('images/icon1.svg') }}" alt="" width="20" height="20" loading="lazy">100% hàng chính hãng</li>
                <li><img src="{{ asset('images/icon2.svg') }}" alt="" width="20" height="16" loading="lazy">Miễn phí giao hàng nội thành HN&amp;HCM</li>
                <li><img src="{{ asset('images/icon3.svg') }}" alt="" width="24" height="20" loading="lazy">Giao hàng toàn quốc</li>
                <li><img src="{{ asset('images/icon4.svg') }}" alt="" width="24" height="20" loading="lazy">7 ngày đổi trả</li>
            </ul>
        </div>
    </div> --}}
</header>

{{-- Lớp phủ đen mờ khi panel mega mở. Đặt NGOÀI header vì .header-hide dùng transform, và
     phần tử transform biến nó thành containing block của position:fixed -> scrim sẽ chỉ phủ
     đúng vùng header. CSS bật display + khoá scroll ở layout.css, JS click để đóng. --}}
<div class="mega-scrim" id="megaScrim"></div>


@include('web/mobile-menu')
