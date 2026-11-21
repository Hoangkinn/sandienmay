@extends('web.layout')

@section('content')
<div class="container mt-4">
    <h1 class="fs-24 fw-7 text-1F1B56 mb-4 pt-11">Sản phẩm liên quan đến: <span class="text-secondary">{{ $currentProduct->name }}</span></h1>
    
    <div class="d-grid template4 listProduct-search" >
        {{-- 1. Danh sách lưới sản phẩm --}}
        <x-fe.products :products="$products" />

        {{-- 2. ĐOẠN THÊM MỚI: Thanh hiển thị phân trang đàng hoàng chuẩn SEO --}}
        @if(!empty($paginate))
            <div class="d-flex justify-content-center mt-4 pt-3 border-top-eee">
                {!! $paginate !!}
            </div>
        @endif
    </div>
</div>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/category.min.css?v=' . filemtime(public_path('css-new/dist/category.min.css'))) }}">
@endpush
