@extends('web.layout')

@section('title', 'Đơn hàng #' . $order->id)
@section('description', 'Chi tiết đơn hàng ' . $order->id . ' của bạn tại Sàn Điện Máy.')

@php $activeNav = 'order'; @endphp

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
                <span class="breadcrumbs-link">Đơn hàng #{{ $order->id }}</span>
            </div>
        </div>
    </div>

    <div class="container mb-4">
        <div class="cust-layout">
            <aside>@include('web.customer._nav')</aside>

            <div class="cust-main">
                <div class="cust-card">
                    <div class="cust-detail-head">
                        <div>
                            <h1 class="cust-h">Đơn hàng #{{ $order->id }}</h1>
                            <p class="cust-order-meta">
                                Đặt ngày {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <span class="cust-status st-{{ $order->status->value }}">{{ $order->status->label() }}</span>
                    </div>

                    <table class="cust-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $goodsTotal = 0; @endphp
                            @foreach ($orderItems as $item)
                                @php
                                    // is_vat cast ra enum Vat nên phải so sánh bằng === (== 0 luôn false).
                                    // Cùng công thức với PaymentController::store: VAT_NO = giá chưa VAT -> cộng 10%.
                                    $lineTotal = $item->is_vat === \App\Enums\Vat::VAT_NO
                                        ? ($item->price + $item->price * 0.1) * $item->quantity
                                        : $item->price * $item->quantity;
                                    $goodsTotal += $lineTotal;
                                @endphp
                                <tr>
                                    <td>
                                        @if ($item->slug)
                                            <a class="cust-item-name"
                                                href="{{ route('frontend.slug', ['slug' => $item->slug]) }}">{{ $item->product_name }}</a>
                                        @else
                                            {{ $item->product_name }}
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->price) }}&nbsp;₫</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($lineTotal) }}&nbsp;₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="cust-summary">
                        <div class="cust-summary-row">
                            <span>Tổng tiền hàng</span>
                            <span>{{ number_format($goodsTotal) }}&nbsp;₫</span>
                        </div>
                        @if ($order->discount_amount > 0)
                            <div class="cust-summary-row">
                                <span>Giảm giá {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span>
                                <span>-{{ number_format($order->discount_amount) }}&nbsp;₫</span>
                            </div>
                        @endif
                        <div class="cust-summary-row is-total">
                            <span>Tổng thanh toán</span>
                            <span>{{ number_format($order->total) }}&nbsp;₫</span>
                        </div>
                    </div>
                </div>

                <div class="cust-card">
                    <h2 class="cust-h2">Thông tin giao hàng</h2>
                    <dl class="cust-info-grid">
                        <div>
                            <dt>Người nhận</dt>
                            <dd>{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt>Điện thoại</dt>
                            <dd>{{ $order->customer_phone }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ $order->customer_email }}</dd>
                        </div>
                        <div>
                            <dt>Địa chỉ</dt>
                            <dd>{{ $order->customer_address }}</dd>
                        </div>
                        @if (!empty($order->customer_note))
                            <div>
                                <dt>Ghi chú</dt>
                                <dd>{{ $order->customer_note }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Thanh toán</dt>
                            <dd>Trả tiền mặt khi nhận hàng (COD)</dd>
                        </div>
                        <div>
                            <dt>Vận chuyển</dt>
                            <dd>Giao hàng toàn quốc, kiểm hàng khi nhận</dd>
                        </div>
                    </dl>

                    <div class="cust-actions">
                        <a class="cust-btn is-ghost" href="{{ route('frontend.customer.orders') }}">Danh sách đơn hàng</a>
                    </div>
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
