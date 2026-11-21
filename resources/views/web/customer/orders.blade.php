@extends('web.layout')

@section('title', 'Đơn hàng của tôi')
@section('description', 'Danh sách đơn hàng bạn đã đặt tại Sàn Điện Máy.')

@php $activeNav = 'orders'; @endphp

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
                <span class="breadcrumbs-link">Đơn hàng của tôi</span>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <div class="cust-layout">
            <aside>@include('web.customer._nav')</aside>

            <div class="cust-main">
                @if (session('success'))
                    <div class="cust-alert">{{ session('success') }}</div>
                @endif

                <div class="cust-card">
                    <h1 class="cust-h">Đơn hàng của tôi</h1>
                    <p class="cust-sub">Tổng {{ $orders->total() }} đơn.</p>

                    @forelse ($orders as $order)
                        <div class="cust-order">
                            <div class="cust-order-head">
                                <div>
                                    <span class="cust-order-id">Đơn #{{ $order->id }}</span>
                                    <span class="cust-order-meta">
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}
                                        · {{ $order->items->count() }} sản phẩm
                                    </span>
                                </div>
                                <span class="cust-status st-{{ $order->status->value }}">{{ $order->status->label() }}</span>
                            </div>
                            <div class="cust-order-body">
                                @foreach ($order->items as $item)
                                    <div class="cust-item">
                                        <span class="cust-item-name">{{ $item->product_name }}</span>
                                        <span class="cust-item-qty">× {{ $item->quantity }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="cust-order-foot">
                                <span class="cust-order-total">{{ number_format($order->total) }}&nbsp;₫</span>
                                <a class="cust-order-link"
                                    href="{{ route('frontend.customer.order', $order->id) }}">Xem chi tiết</a>
                            </div>
                        </div>
                    @empty
                        <div class="cust-empty">
                            Bạn chưa có đơn hàng nào. <a href="{{ route('frontend.page.home') }}">Mua sắm ngay</a>
                        </div>
                    @endforelse

                    @if ($orders->lastPage() > 1)
                        <div class="cust-pager">
                            <nav aria-label="Phân trang đơn hàng">
                                @if ($orders->onFirstPage())
                                    <span aria-hidden="true">‹</span>
                                @else
                                    <a href="{{ $orders->previousPageUrl() }}" rel="prev">‹</a>
                                @endif

                                @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                    @if ($page == $orders->currentPage())
                                        <span aria-current="page">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if ($orders->hasMorePages())
                                    <a href="{{ $orders->nextPageUrl() }}" rel="next">›</a>
                                @else
                                    <span aria-hidden="true">›</span>
                                @endif
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts-header')
    <meta name="robots" content="noindex, nofollow">
@endpush

@push('page-css')
    <link rel="stylesheet"
        href="{{ asset('css-new/dist/customer.min.css?v=' . filemtime(public_path('css-new/dist/customer.min.css'))) }}">
@endpush
