@extends('web.layout')

@section('title', 'Chi tiết đơn hàng')
@section('description', $settings[4]['setting_value'])
@section('keywords', $settings[3]['setting_value'])

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
            <span class="breadcrumbs-link">Chi tiết đơn hàng</span>
        </div>
    </div>
</div>
<div class="container mb-4">
    <div class="row g-1 g-lg-4 ">
        <div class="bg-fff rounded-10 p-10">
            <h1>Chi tiết đơn hàng</h1>
<table>
    <tbody>
        <tr>
            <th>Sản phẩm</th>
            <th>Tổng</th>
        </tr>
        @php $total = 0 @endphp
        @foreach ($orderItems as $orderItem)
        @php 
            $itemTotalWithVat = $orderItem->is_vat == 0 
                ? (($orderItem->price + ($orderItem->price * 0.1)) * $orderItem->quantity) 
                : ($orderItem->price * $orderItem->quantity);
            $total += $itemTotalWithVat;
        @endphp
        <tr>
            <td class="p-5">
                <a class="text-D33 fs-16" href="{{ route('frontend.slug', ['slug' => $orderItem->slug]) }}">
                    {{ $orderItem->product_name }}
                </a>
                
            </td>
            <td class="p-5">
                <strong>Số lượng: &nbsp;{{ $orderItem->quantity }}</strong>
            </td>
        </tr>
        @endforeach

        @php
            // Tính tổng tiền gốc chưa VAT từ các item
            $subTotalNoVat = $orderItems->sum(function($item) {
                return $item->price * $item->quantity;
            });
            // Tổng tiền hàng đã gồm VAT
            $subTotalWithVat = $subTotalNoVat * 1.1;
        @endphp

        <!-- Tổng tiền hàng (chưa VAT) -->
        <tr>
            <td class="p-5"><strong>Tổng tiền hàng (chưa VAT):</strong></td>
            <td class="p-5">{{ number_format($subTotalNoVat) }}&nbsp;₫</td>
        </tr>

        <!-- Tổng tiền hàng (đã bao gồm VAT) -->
        <tr>
            <td class="p-5"><strong>Tổng tiền hàng (đã bao gồm VAT):</strong></td>
            <td class="p-5"><b>{{ number_format($subTotalWithVat) }}</b>&nbsp;₫</td>
        </tr>

        <!-- Giảm giá nếu có -->
        @if(isset($order->discount_amount) && $order->discount_amount > 0)
        <tr>
            <td class="p-5">
                <strong>Giảm giá:</strong>
                @if(!empty($order->coupon_code))
                    <span class="badge badge-info">{{ $order->coupon_code }}</span>
                @endif
            </td>
            <td class="p-5 text-danger"><b>-{{ number_format($order->discount_amount) }}</b>&nbsp;₫</td>
        </tr>
        @endif

        <!-- Tổng tiền thanh toán cuối cùng -->
        <tr>
            <td class="p-5"><strong>Tổng tiền thanh toán:</strong></td>
            <td class="p-5">
                <b style="color: #dc3545; font-size: 1.1em;">{{ number_format($order->total) }}</b>&nbsp;₫
            </td>
        </tr>
    </tbody>
</table>
            <div>
                <p>
                    <strong>Cảm ơn bạn. Đơn hàng của bạn đã được nhận.</strong>
                </p>
                <ul>
                    <li>
                        Mã đơn hàng: <strong>#{{ $order->id }}</strong>
                    </li>
                    <li>
                        Ngày: <strong>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</strong>
                    </li>
                    <li>
                        Tổng tiền thanh toán: <strong>
                            <span>
                                <bdi>{{ number_format($order->total) }}&nbsp;<span>₫</span> <small>(Giá bao gồm VAT 10%)</small></bdi>
                            </span>
                        </strong>
                    </li>
                    <li>
                        Phương thức thanh toán: <strong>Trả tiền mặt khi nhận hàng (COD)</strong>
                    </li>
                    <li>
                        Giao nhận hàng: <strong>Giao hàng Toàn Quốc, nhận hàng được kiểm hàng</strong>
                    </li>
                </ul>
            </div>
            <div class="text-center my-3">
                @auth('customers')
                    <a class="rounded-10 fw-7 py-10 px-30 cta-D80000" style="display: inline-block; margin: 0 8px 8px;"
                        href="{{ route('frontend.customer.order', $order->id) }}">THEO DÕI ĐƠN HÀNG</a>
                @else
                    <a class="rounded-10 fw-7 py-10 px-30 cta-D80000" style="display: inline-block; margin: 0 8px 8px;"
                        href="{{ route('frontend.customer.register') }}">ĐĂNG KÝ ĐỂ THEO DÕI ĐƠN HÀNG</a>
                @endauth
                <a class="rounded-10 fw-7 bg-1F1B56 text-fff py-10 px-30 text-fff-hover cta-D80000" style="display: inline-block; margin: 0 8px 8px;" href="{{route('frontend.page.home')}}">QUAY LẠI TRANG CHỦ</a>
            </div>
        </div>
    </div>
</div>
@stop

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/cart.min.css?v=' . filemtime(public_path('css-new/dist/cart.min.css'))) }}">
@endpush
