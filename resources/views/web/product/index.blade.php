@php use App\Enums\ProductCategoryFilterPrice;use App\Enums\ProductCategorySort;use App\Helpers\BreadcrumbHelper; @endphp
@extends('web.layout')

@section('content')
{!! BreadcrumbHelper::render() !!}
<div class="container px-0">
    <div class="d-flex align-items-center my-2">
        <h1 class="fs-15 fs-lg-18 text-uppercase">Tất cả sản phẩm</h1>
        <span class="fs-14 text-000 ms-2 fw-4">{{$total}} sản phẩm</span>

    </div>
    <div class="my-3 bg-fff rounded-10 overflow-hidden p-10">
        <span class="fs-lg-20 fs-14 text-1F1B56 p-0 fw-6">Danh mục sản phẩm</span>
        <div class="glide position-relative fw-6 lg-external-btn mt-3"
            data-perview="2" data-pc-perview="6" data-autoplay="0">
            <div class="overflow-hidden" data-glide-el="track">
                <div class="row mx-0 gx-2 gx-lg-3 flex-nowrap py-2 py-lg-3">
                    @foreach($relatedCat as $i)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ $i['url'] }}" class="d-flex flex-column h-100 align-items-center text-center ">
                            <div class="page-cat-image thumb-cover bg-F2F4F7 rounded-circle p-10 bg-1F1B56-hover">
                                <img src="{{ $i['image'] }}" alt="{{ $i['name'] }}">
                            </div>
                            <span
                                class="text-000 fs-16 fw-6 mt-3">
                                {{ $i['name'] }}
                            </span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @if(count($relatedCat) > 6)
            <div data-glide-el="controls">
                <span data-glide-dir="<" class="cursor-pointer btn-prev position-absolute top-50 translate-middle-y start-0 ms-1 " style="background-color: unset; top: 38%; left: -10px">
                    <i class="icon-left fs-8 fs-lg-32 p-2 btn-next-slider rounded-circle p-5 text-fff-hover"></i>
                </span>
                <span data-glide-dir=">" class="cursor-pointer btn-next position-absolute top-50 translate-middle-y end-0 me-1 " style="background-color: unset; top: 38%; right: -10px">
                    <i class="icon-right fs-8 fs-lg-32 p-2 btn-next-slider rounded-circle p-5 text-fff-hover"></i>
                </span>
            </div>
            @endif
        </div>
    </div>

    <form method="get" id="formFilter" action="{{ route('frontend.page.product') }}">
        <div class=" bg-fff rounded-10 p-10 mb-3">
            <div class="fs-lg-16 fs-14 text-000 p-0 fw-6 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                Sắp xếp theo
            </div>
            <div class="d-flex pb-5 gap-2 flex-wrap fs-12 fs-lg-14 mt-2">
                @foreach(ProductCategoryFilterPrice::cases() as $i)
                <label
                    class="text-nowrap btn-hover cursor-pointer rounded-5 py-10 px-20 fw-6 fs-14 text-fff-hover bg-1F1B56-hover {{ $filterPriceEnum==$i ? 'bg-fff' : 'bg-F2F4F7' }}"
                    style="border: 1px solid #939393;">
                    <input type="radio" hidden name="price" @checked($filterPriceEnum==$i) onClick="this.form.submit()"
                        value="{{ $i->value }}">
                    {{ $i->getTitle() }}
                </label>
                @endforeach
                @foreach(ProductCategorySort::cases() as $i)
                <label
                    class="text-nowrap btn-hover cursor-pointer rounded-5 py-10 px-20 fw-6 fs-14 text-fff-hover bg-1F1B56-hover {{ $sortEnum==$i ? 'bg-fff' : 'bg-F2F4F7' }}"
                    style="border: 1px solid #939393;">
                    <input type="radio" hidden name="sort" onClick="this.form.submit()" @checked($sortEnum==$i)
                        value="{{ $i->value }}">
                    {{ $i->getTitle() }}
                </label>
                @endforeach
            </div>
        </div>
    </form>

    <div class="col-lg-12 pb-10">
        <div class="row row-cols-2 row-cols-lg-5 g-3" id="listProduct">
            @if(!empty($products))
            <x-fe.products :products="$products" :preload="true" />
            @else
            <p>0 có sản phẩm nào!</p>
            @endif
        </div>
        {!! $paginate !!}
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Common.loadJs('CustomGlide.min.js').then(() => {
            CustomGlide.initAll()
        })
    })
</script>
@endpush

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/category.min.css?v=' . filemtime(public_path('css-new/dist/category.min.css'))) }}">
@endpush
