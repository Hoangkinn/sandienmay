{{-- Drawer menu mobile — cấu trúc theo mẫu trungtammuasam.vn (#modelMenuMobi):
     thanh logo + ô tìm kiếm + danh mục cấp 1 dạng accordion (dùng lại <x-fe.menu>) --}}
<dialog id="modelMenuMobi" class="mobile-menu" aria-label="Danh mục sản phẩm">
    <div class="mobile-menu-head">
        <a href="{{ url('/') }}" aria-label="Trang chủ DQC-TECH">
            <img src="{{ asset('images/logo.svg') }}" alt="Sàn điện máy" width="100" height="70" loading="lazy">
        </a>
    </div>

    <form class="mobile-menu-search" action="{{ \App\Helpers\GetFeUrlHelper::search() }}">
        <input type="search" name="s" placeholder="Bạn tìm gì hôm nay?" aria-label="Tìm kiếm sản phẩm" required>
        <button type="submit" aria-label="Nút tìm kiếm">
            <img src="{{ asset('images/search.png') }}" alt="Tìm kiếm" width="16" height="16" loading="lazy">
        </button>
    </form>

    <nav class="mobile-menu-nav">
        <x-fe.menu></x-fe.menu>
    </nav>
</dialog>
