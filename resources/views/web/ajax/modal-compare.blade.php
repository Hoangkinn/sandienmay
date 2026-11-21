<div class="row gy-2 pb-2 bg-515151">
    @for($i=0; $i<=2; $i++)
        @php
        $product = $products[$i] ?? [];
        @endphp
        <div class="col-11 col-lg-3 flex-lg-grow-1">
            <div class="position-relative bg-white">
                @if($product)
                    <span class="position-absolute bottom-0 end-0 cursor-pointer" onclick="ModalCompareProduct.remove({{ $i }})">
                        <i class="icon-close"></i>
                    </span>
                @endif

                @include('web.ajax._modal-compare', [
                'product' => $product
                ])
            </div>
        </div>
    @endfor

    <div class="col-11 col-lg-auto">
        <button class="border col-12 p-7 bg-1F1B56 rounded-4 cta-D80000 cursor-pointer" @if(count($products) > 1) onclick="ModalCompareProduct.submit()" @endif>So sánh ngay</button>
        <button class="border col-12 p-7 mt-2 mb-5 mb-lg-0 rounded-4 cta-D80000 cursor-pointer" @if(count($products)) onclick="ModalCompareProduct.remove()" @endif>Xóa tất cả</button>
    </div>
</div>
