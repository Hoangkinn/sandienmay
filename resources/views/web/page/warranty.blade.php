@extends('web.layout')
@section('content')
<div class="breadcrumbs" style="margin-bottom: 30px">
    <div class="container">
        <div class="breadcrumbs-main d-flex gap10">
            <a class="breadcrumbs-home d-flex gap05" href="{{ route('frontend.page.home') }}">
                Trang chủ
            </a>
            <div class="breadcrumbs-icon">
                <img src="images/ic-right.svg" width="8" height="14" alt="Mũi tên phân cách breadcrumb">
            </div>
            <span class="breadcrumbs-link">Tra cứu bảo hành</span>
        </div>
    </div>
</div>
<div class="container g-1 g-lg-0 mb-4 bg-fff rounded-10 py-20 text-center">
    <div class="">
        <p class="fs-20 fw-6 text-1F1B56">Kiểm tra thông tin bảo hành <br>
            Tra cứu thông tin và thời hạn bảo hành sản phẩm của bạn</p>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-2">
        <input class="rounded-5 border border-D9D9D9 px-5 placeholder-a9a9a9 fs-16" style="width: 400px; height: 65px" type="text" placeholder="Nhập số seri sản phẩm của bạn. Vidu: KMS77702"> 
        <button type="submit" class=" rounded-5 bg-D33 text-fff border fs-18 fw-6 cta-D80000" style="width: 120px; height: 65px">Kiểm tra</button>
    </div>
</div>
@stop

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/page.min.css?v=' . filemtime(public_path('css-new/dist/page.min.css'))) }}">
@endpush
