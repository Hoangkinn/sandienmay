@php use App\Helpers\BreadcrumbHelper; @endphp
@extends('web.layout')

@section('content')
{!! BreadcrumbHelper::render() !!}
<style>
    .table-compare {
        th:first-child {
            text-align: left;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: .5rem;
        }
    }
</style>
<div class="container g-2 g-lg-0 p-20 mb-5 bg-fff rounded-10">
    <div class="overflow-auto">
        <table class="table-compare">
            <tr>
                <th></th>
                @foreach($products as $p)
                <th>{{ $p['name'] }}</th>
                @endforeach
            </tr>

            <tr>
                <th></th>
                @foreach($products as $k => $product)
                <td>
                    <div class="text-center thumb-cover product-thumb-compare">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                    </div>
                    <div class="d-flex gap-3 align-items-center mt-2">
                        @if(!empty($product['sale_price']))
                        <span class="fs-lg-24 fw-7 text-D33">{{ number_format($product['sale_price'], 0, ',', '.') }}đ</span>
                        <span class="product-item-price-sale fs-18 ms-2 text-decoration-line-through" style="text-decoration: line-through;">{{number_format($product['regular_price'], 0, ',', '.')}}đ</span>
                        @if (!empty($product['discount_percent']))
                        <span class="ms-2 fs-16 text-D33">(-{{ $product['discount_percent'] }}%)</span>
                        @endif
                        @else
                        <span class="fs-24 fs-lg-24 fw-7 text-D33">{{ $product['regular_price'] ? number_format($product['regular_price'], 0, ',', '.').'đ' : "Liên hệ" }}</span>
                        @endif
                    </div>
                    <div class=" fs-12 fs-lg-16">
                        <x-fe.show-vote :avg="$reviewAvgs[$k]"></x-fe.show-vote>
                        ({{ $reviewAvgs[$k] }} đánh giá)
                    </div>
                </td>
                @endforeach
            </tr>
            <tr>
                <th>Ưu đãi</th>
                @foreach($products as $k => $product)
                <td>
                    {!! str_replace('<ul', '<ul class="m-0 py-10"' , $product['promotion']) !!}
                </td>
                @endforeach
            </tr>

            @foreach($keys as $key)
                <tr>
                    <th>{{ $key }}</th>

                    @foreach($products as $k => $p)
                        @php
                            // Có thể 1 sản phẩm không có attribute này
                            $values = $attributeList[$k][$key] ?? [];
                        @endphp
                        <td>
                            @if(!empty($values))
                                {{ implode(', ', array_column($values, 'value')) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td></td>
                @foreach($products as $p)
                <td>
                    <a class="fw-6 fs-lg-18 fs-16 text-000" href="{{ $p['url'] }}">Xem sản phẩm: {{ $p['name'] }}</a>
                    <div class="rounded-10 fw-7 bg-1F1B56 text-fff py-10 px-lg-30 px-10 text-A9A9A9-hover text-center"><a style="padding: 7px 15px; border-radius: 5px;" class="cta-D80000" href="{{$p['url']}}"> XEM CHI TIẾT</a></div>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
</div>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/category.min.css?v=' . filemtime(public_path('css-new/dist/category.min.css'))) }}">
@endpush
