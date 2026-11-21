@extends('web.layout')

@section('content')
<div class="container mt-4">
    <h1 class="fs-24 fw-7 text-1F1B56 mb-4">Sản phẩm bạn đã xem gần đây</h1>
    
    <div class="d-grid template4 listProduct-search">
        <x-fe.products :products="$products" />
    </div>
</div>
@endsection

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/category.min.css?v=' . filemtime(public_path('css-new/dist/category.min.css'))) }}">
@endpush
