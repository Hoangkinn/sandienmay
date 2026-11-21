@php
if ($type == 'search'){
$fontSizeTitle = 'fs-12';
$fontSizePrice = 'fs-14';
} else {
$fontSizeTitle = 'fs-14';
$fontSizePrice = 'fs-14 fs-lg-16';
}
@endphp
@foreach($categories as $category)
<div class="d-flex justify-content-between align-items-center position-relative py-10 border-bottom">
    <div class="d-flex align-items-center">
        <img src="{{ $category['image'] }}" width="50" loading="lazy" alt="icon danh mục {{ $category['name'] }}">
        <a href="{{ $category['url'] }}" class="stretched-link text-000 ms-3 fs-14">{{ $category['name'] }}</a>
    </div>
    {{-- <span>Xem thêm</span> --}}
</div>
@endforeach
@foreach($products as $product)
<div class="py-10 d-flex position-relative border-bottom">
    <div>
        <img src="{{ $product['image'] }}" width="60" height="60" alt="{{ $product['name'] }}">
    </div>
    <div class="flex-grow-1 ps-4 ms-2">
        <h2 class="{{ $fontSizeTitle }}">
            <a class="stretched-link text-000 fs-14" href="{{ $product['url'] }}" title="{{ $product['name'] }}">{{ $product['name'] }}</a>
        </h2>
        @if(!empty($product['sale_price']))
        <span class="fs-14 fs-lg-16 fw-7 price-regular">{{ number_format($product['sale_price'], 0, ',', '.') }}đ</span>
        <span class="product-item-price-sale fs-12 fw-5">{{number_format($product['regular_price'], 0, ',', '.')}}đ</span>
        @else
        <span class="fs-14 fs-lg-16 fw-7 price-regular">{{ $product['regular_price'] ? number_format($product['regular_price'], 0, ',', '.').'đ' : "Liên hệ" }}</span>
        @endif
    </div>
</div>
@endforeach