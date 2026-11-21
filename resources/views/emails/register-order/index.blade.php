<h3>Thông tin đặt hàng</h3>
<p>Tên khách hàng: <b>{{ $order->customer_name }}</b></p>
<p>Email: <b>{{ $order->customer_email }}</b></p>
<p>Điện thoại: <b>{{ $order->customer_phone }}</b></p>
<p>Địa chỉ: <b>{{ $order->customer_address }}</b></p>
<p>Ghi chú: <b>{{ $order->customer_note }}</b></p>

<h3>Chi tiết đơn hàng</h3>
<table style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd;text-align: center;">Sản phẩm</th>
            <th style="border: 1px solid #ddd;text-align: center;">Giá</th>
            <th style="border: 1px solid #ddd;text-align: center;">Tổng</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($order->items as $item)
        <tr>
            <td style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;">
                <a href="{{ route('frontend.slug', ['slug' => $item->product()->slug]) }}">{{ $item->product_name }} </a> <strong> × {{ $item->quantity }}</strong>
            </td>
            <td style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;">
                <span>{{ number_format($item->price) }} ₫</span>
            </td>
            <td style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;">
                <span>{{ number_format($item->price * $item->quantity) }} ₫</span>
                <br> <small>({{ $item->is_vat->label() }})</small>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;">Không có sản phẩm nào</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;">Tổng tiền:</th>
            <td colspan="2" style="border: 1px solid #ddd;text-align: left;padding: 5px 10px;"><b>{{ number_format($order->total) }} ₫</b> <br><small>(Giá đã bao gồm VAT 10%)</small></td>
        </tr>
    </tfoot>
</table>
