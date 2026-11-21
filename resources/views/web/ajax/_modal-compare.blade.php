@if($product)
    <div class="border border-d3d3d3 overflow-hidden rounded-4 py-2 px-2 d-flex gap-3 bg-fff">
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" width="60">
        <div class="">
            <div>{{ $product['name'] }}</div>
            <div class="d-flex gap-2 align-items-center">
                @if(!empty($product['sale_price']))
                <span class="fs-16 fw-7 text-D33">{{ number_format($product['sale_price'], 0, ',', '.') }}đ</span>
                <span class="product-item-price-sale fs-14 text-decoration-line-through" style="text-decoration: line-through;">{{number_format($product['regular_price'], 0, ',', '.')}}đ</span>
                @else
                <span class="fs-16 fw-7 text-D33">{{ $product['regular_price'] ? number_format($product['regular_price'], 0, ',', '.').'đ' : "Liên hệ" }}</span>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="border rounded-4 p-2 d-flex gap-3 flex-center cursor-pointer bg-fff border-d3d3d3" style="border-style: dotted" onclick="ModalCompareSelectProduct.showModal()">
        <div class="text-center">
            <div>Chọn thêm sản phẩm</div>
        </div>
    </div>
@endif

