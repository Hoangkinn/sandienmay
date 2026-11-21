@extends('web.layout')
@once
    @php
        $adminCustomSchema = null;
        if (isset($page)) {
            if (is_array($page)) {
                $adminCustomSchema = $page['schema'] ?? null;
            } elseif (is_object($page)) {
                $adminCustomSchema = $page->schema ?? null;
            }
        }
        $adminCustomSchema = is_string($adminCustomSchema) ? trim($adminCustomSchema) : null;
    @endphp

    @if(!empty($adminCustomSchema))
        @section('default-schema')
            {{-- Để trống để ẩn 3 schema cứng ở layout khi có custom schema --}}
        @endsection
    @endif

    @push('schema')
        @if(!empty($adminCustomSchema))
            {{-- Trường hợp 1: Nếu admin có nhập custom schema -> Chỉ hiển thị của admin --}}
            @if(str_starts_with($adminCustomSchema, '<script'))
                {!! $adminCustomSchema !!}
            @else
                <script type="application/ld+json">
                {!! $adminCustomSchema !!}
                </script>
            @endif
        @else
            {{-- Trường hợp 2: Tự động render BreadcrumbList cho trang tĩnh (Page) --}}
            @php
                $pageTitle = $page['title'] ?? ($page->title ?? 'Trang chủ');
                $pageUrl = $page['url'] ?? ($page->url ?? url()->current());

                $breadcrumbItems = [
                    [
                        "@type" => "ListItem",
                        "position" => 1,
                        "name" => "Trang chủ",
                        "item" => route('frontend.page.home'),
                    ],
                    [
                        "@type" => "ListItem",
                        "position" => 2,
                        "name" => $pageTitle,
                        "item" => $pageUrl,
                    ]
                ];

                $breadcrumbJson = [
                    "@context" => "https://schema.org",
                    "@type" => "BreadcrumbList",
                    "itemListElement" => $breadcrumbItems,
                ];
            @endphp

            <script type="application/ld+json">
            {!! json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
    @endpush
@endonce
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
            <span class="breadcrumbs-link">{{$page['title']}}</span>
        </div>
    </div>
</div>
<div class="container g-1 g-lg-0 mb-4">
    <div class="row g-1 g-lg-4">
        <div class="col-lg-12">
            <div class="bg-fff rounded-10 p-20">
                <div class="content">
                    {!! $page['content'] !!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/page.min.css?v=' . filemtime(public_path('css-new/dist/page.min.css'))) }}">
@endpush
