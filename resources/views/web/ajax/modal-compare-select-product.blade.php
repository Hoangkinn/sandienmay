@foreach($products as $product)
    <div class="col-12 col-lg-6">
        <div class="row g-2 align-items-center">
            <div class="col-9 col-sm-10">
                @include('web.ajax._modal-compare', ['product' => $product])
            </div>
            <div class="col-3 col-sm-2 text-end">
                <button style="min-width:64px; white-space:nowrap;" class="cta-D80000 border cursor-pointer p-2 bg-D9D9D9 bg-1F1B56-hover rounded-4 jsShowModalCompareProduct" data-id="{{ $product['id'] }}">Chọn</button>
            </div>
        </div>
    </div>
@endforeach
