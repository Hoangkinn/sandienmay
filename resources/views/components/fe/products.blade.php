@pushif($preload, 'preload')
<link rel="preload" href="{{ $products[0]['image'] }}" as="image" fetchpriority="high">
@endpushif
@foreach ($products as $k => $product)
    <div class="product-card item-product">
        <a href="{{ $product['url'] }}" class="product-card-link-overlay" title="{{ $product['name'] }}"></a>

        <a href="{{ $product['url'] }}" class="product-image-wrapper" title="{{ $product['name'] }}">
            <img class="product-img-main" src="{{ $product['image'] }}" width="214" height="214"
                alt="{{ $product['name'] }}"
                @if (!$k && $preload) loading="eager" decoding="sync" fetchpriority="high" @else loading="lazy" @endif>
            @if (!empty($product['images'][1]))
                <img class="product-img-hover" src="{{ $product['images'][1] }}" width="214" height="214"
                    alt="Ảnh khác của {{ $product['name'] }}" loading="lazy">
            @endif

            @if ($quickView)
                {{-- Tam thoi bo So sanh: chi con Xem nhanh, giua o anh --}}
                <span class="card-actions">
                    <span class="view-product-smart jsShowModalDetailProduct">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M20 20l-3.9-3.9M11 8v6M8 11h6" />
                        </svg>
                    </span>
                </span>
            @endif
        </a>

        <h3 class="product-name"><a href="{{ $product['url'] }}" title="{{ $product['name'] }}">{{ $product['name'] }}</a></h3>

        <div class="price-info">
            @if (!empty($product['sale_price']))
                <span class="current-price">{{ number_format($product['sale_price'], 0, ',', '.') }}đ</span>
                <span class="old-price">{{ number_format($product['regular_price'], 0, ',', '.') }}đ</span>
            @else
                <span class="current-price">{{ $product['regular_price'] ? number_format($product['regular_price'], 0, ',', '.') . 'đ' : 'Liên hệ' }}</span>
            @endif

            @if (!empty($product['discount_percent']))
                <span class="discount-flag"><b>{{ $product['discount_percent'] }}%</b><em>Giảm</em></span>
            @endif
        </div>

        @if ($viewSoldQuantity && !empty($product['sold_quantity']))
            <div class="status-tag">
                <div class="status-tag-line" style="width: {{ min(($product['sold_quantity'] / 300) * 100, 100) }}%"></div>
                <p>Đã bán {{ $product['sold_quantity'] }}</p>
            </div>
        @endif

        {{-- Nut Xem nhanh da chuyen vao giua o anh o phan tren --}}
    </div>
@endforeach


@pushonce('lazyContent')
    <dialog id="modalDetailProduct" class="col-12 border-0 shadow-none outline-0 p-3 g-3  bg-fff">
        <div class="gx-2 gx-xl-4 showContent">

        </div>
        <button type="button" class="qv-close" data-target-modal data-close="modalDetailProduct" aria-label="Đóng">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </dialog>
    {{-- Tam thoi bo html so sanh
    <dialog id="modalCompareProduct"
        class="col-12 border-0 shadow-none outline-0 g-3 position-fixed bottom-0 mb-lg-5 z-3 bg-fff" style="padding: 10px 15px">
        <div class="container gx-2 gx-xl-4 shadow  showContent pe-25">

        </div>
        <span class="p-5 position-absolute cursor-pointer" data-target-modal
            data-close="modalCompareProduct" style="top: -28px; right: -26px; color: #d50118">
            <i class="icon-close fs-30" style="box-shadow: 0 0px 3px 0 rgb(60 64 67 / 58%), 0 0px 7px 0px rgba(60, 64, 67, 0.55);
    border-radius: 100px;"></i>
        </span>
    </dialog>
    <dialog id="modalCompareSelectProduct" class="col-12 col-lg-10 border-0 shadow-none outline-0 p-1 p-lg-3 g-3 bg-fff">
        <div class="container gx-2 gx-xl-4">
            <span class="p-2 position-absolute top-0 end-0 cursor-pointer fs-20" data-target-modal
                data-close="modalCompareSelectProduct">
                <i class="icon-close"></i>
            </span>
            <div class="text-center fw-6 p-3 border-bottom">Thêm sản phẩm so sánh</div>
            <div class="p-1 p-lg-3">
                <div class="p-2">Tìm sản phẩm</div>
                <div class="border rounded-4 p-2 mb-3">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Nhập tên sản phẩm" class="border-0">
                </div>
                <div class="showContent row g-2" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;"></div>
            </div>
        </div>
    </dialog>
    --}}
@endpushonce
@pushonce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Common.loadJs('_modalDetailProduct.js');
            Common.loadJs('_wishlist.js');
        });
    </script>
@endpushonce
