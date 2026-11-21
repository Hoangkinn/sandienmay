<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hóa đơn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ _('Trang chủ') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.order.index') }}">{{ _('Danh sách đơn hàng') }}</a></li>
                        <li class="breadcrumb-item active">{{ _('Chi tiết hóa đơn') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3">
                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    <i class="fas fa-globe"></i> Sàn điện máy
                                    <small class="float-right">Ngày:
                                        {{ $order->created_at->format('d/m/Y H:i:s') }}</small>
                                </h4>
                            </div>
                        </div>
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                Từ
                                <address>
                                    <strong>CÔNG TY TNHH KUMISAI VIỆT NAM</strong><br>
                                    Ngõ 65 Đường Lăng Hiển Linh, Thôn 1, Xã Lại Yên, Huyện Hoài Đức, Thành phố Hà Nội, Việt Nam<br>
                                    Số điện thoại: 0982 090 819 - 0983 898 758<br>
                                    Email: info@kumisai.vn
                                </address>
                            </div>

                            <div class="col-sm-4 invoice-col">
                                Tới
                                <address>
                                    Họ tên: <strong>{{ $order->customer_name }}</strong><br>
                                    Địa chỉ: {{ $order->customer_address }}<br>
                                    Điện thoại: {{ $order->customer_phone }}<br>
                                    Email: {{ $order->customer_email }}<br>
                                    Ghi chú: {{ $order->customer_note }}
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <b>Mã hóa đơn:</b> #{{ $order->id }}<br>
                                <b>Trạng thái:</b>
                                @if ($order->status->value != App\Enums\OrderStatus::COMPLETED->value)
                                <form method="post" action="{{ route('backend.order.updateStatus', ['id' => $order->id]) }}" class="form-inline">
                                    @csrf
                                    <select name="status" class="form-control form-control-sm"
                                        data-status="pending">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($order->status->value == $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" name="submit" value="change_status" class="btn btn-sm btn-primary ml-2">Thay đổi
                                    </button>
                                </form>
                                @else
                                    {!! $order->getStatusName() !!}
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Số lượng</th>
                                            <th>Giá</th>
                                            <th>Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $total = 0 @endphp
                                        @foreach ($order->items as $item)
                                            @php $total += $item->price * $item->quantity @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ number_format($item->price) }}<span>₫</span> <br> <small>({{ $item->is_vat->label() }})</small></td>
                                                <td>{{ number_format($item->price * $item->quantity) }}<span>₫</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <p class="lead">{{ _('Phương thức thanh toán') }}:</p>
                                @php $banks = ['card1', 'card2', 'card3', 'card4'] @endphp
                                @foreach ($banks as $bank)
                                        <img src="{{ asset('images/' . $bank . '.png') }}" width="92" height="51" alt="{{ $bank }}">
                                @endforeach
                                <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                                    Trả tiền mặt khi giao hàng.
                                </p>
                            </div>
                            {{-- <div class="col-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th style="width:50%">Tổng tiền:</th>
                                                <td><b>{{ number_format($total) }}</b><span>₫</span> (Giá chưa bao gồm VAT)</td>
                                            </tr>
                                            <tr>
                                                <th>Phí vận chuyển:</th>
                                                <td>0<span>₫</span></td>
                                            </tr>
                                            <tr>
                                                <th>Tổng tiền thanh toán:</th>
                                                <td><b>{{ number_format($total * (1+0.10)) }}</b><span>₫</span> (Giá đã bao gồm VAT)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> --}}
                            <div class="col-6">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            @php
                                                // Tổng tiền hàng gốc chưa VAT (lấy tổng tiền các sản phẩm)
                                                $subTotalNoVat = $order->items->sum(function($item) {
                                                    return $item->price * $item->quantity;
                                                });

                                                // Tổng tiền hàng sau khi đã cộng 10% VAT
                                                $subTotalWithVat = $subTotalNoVat * 1.1;
                                            @endphp

                                            <tr>
                                                <th style="width:50%">Tổng tiền hàng (chưa VAT):</th>
                                                <td>{{ number_format($subTotalNoVat) }}<span>₫</span></td>
                                            </tr>
                                            <tr>
                                                <th>Tổng tiền hàng (đã bao gồm VAT):</th>
                                                <td><b>{{ number_format($subTotalWithVat) }}</b><span>₫</span></td>
                                            </tr>

                                            {{-- Hiển thị mã giảm giá nếu có --}}
                                            @if(isset($order->discount_amount) && $order->discount_amount > 0)
                                            <tr>
                                                <th>
                                                    Giảm giá:
                                                    @if(!empty($order->coupon_code))
                                                        <span class="badge badge-info">{{ $order->coupon_code }}</span>
                                                    @endif
                                                </th>
                                                <td class="text-danger"><b>-{{ number_format($order->discount_amount) }}</b><span>₫</span></td>
                                            </tr>
                                            @endif

                                            <tr>
                                                <th>Phí vận chuyển:</th>
                                                <td>0<span>₫</span></td>
                                            </tr>

                                            <tr>
                                                <th>Tổng tiền thanh toán:</th>
                                                <td><b style="color: #dc3545; font-size: 1.1em;">{{ number_format($order->total) }}</b><span>₫</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row no-print">
                            <div class="col-12">
                                <a href="{{ route('backend.order.print', ['id' => $order->id]) }}" rel="noopener"
                                    target="_blank" class="btn btn-default">
                                    <i class="fas fa-print"></i> In hóa đơn
                                </a>
                                <a href="{{ route('backend.order.index') }}" rel="noopener"
                                    class="btn btn-secondary float-right">
                                    {{ _('Quay lại') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
