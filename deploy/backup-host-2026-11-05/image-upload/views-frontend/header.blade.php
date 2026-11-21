<style>
    @media (max-width: 768px) {
    .main-nav {
        margin-top: 10px;
    }
}
</style>
<header class="site-header">
    <div class="container">
        <h1 class="logo">
            <a href="{{ url('/') }}" aria-label="Trang chủ DQC-TECH">
                <img src="{{ asset('images/logo-header-v3.png?ver=3') }}" alt="DQC-TECH - Giải pháp toàn diện" width="210" height="48" loading="eager" fetchpriority="high">
            </a>
        </h1>

        <search class="search-box">
            <form action="{{ \App\Helpers\GetFeUrlHelper::search() }}" class="formSearch position-relative" data-skip-ajax>
                <input type="search" name="s" placeholder="Bạn tìm gì hôm nay?" aria-label="Tìm kiếm sản phẩm" required>
                <button type="submit" aria-label="Nút tìm kiếm">
                    <img src="{{ asset('images/search.png') }}" alt="tìm kiếm" width="20" height="20" loading="eager">
                </button>
                <div class="bg-fff searchResult position-absolute top-100 start-0 col-12 mh-350 overflow-auto px-10 z-3"></div>
            </form>
        </search>

        <div class="header-actions">
            <a href="tel:{!! $phoneHn !!}" class="hotline" aria-label="Hotline DQC-TECH">
                <img src="{{ asset('images/phone-call.svg') }}" alt="Hotline" width="24" height="24" loading="eager">
                <span>Hotline <br> <b>{!! $phoneHn !!}</b></span>
            </a>
            
            <a href="{{ route('frontend.cart.index') }}" class="cart-link" aria-label="Giỏ hàng">   
                <img src="{{ asset('images/shopping-cart.svg') }}" alt="Giỏ hàng" width="24" height="24" loading="eager">
                <span>Giỏ <br> hàng</span>
                <span class="jsCountCart text-fff">
                            @if(session('carts'))
                            {{ collect(session('carts'))->sum('quantity') }}
                            @else
                            0
                            @endif
                        </span>
            </a>

            <button class="menu-link" id="menu-toggle" aria-label="Menu">
                <img src="{{ asset('images/menu.svg') }}" alt="Menu" width="24" height="24" loading="eager">
            </button>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container">
            <x-fe.menu></x-fe.menu>
        </div>
    </nav>
</header>