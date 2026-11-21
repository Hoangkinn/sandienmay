@extends('web.layout')
@section('css')
<style>
    .btn-remove-cart {
        display: inline-block;
        padding: 5px 35px 6px 0;
        background: url(../img/bg-cart-delete.png) right top no-repeat;
        color: #131313;
        font-size: 14px;
        font-weight: bold;
    }

    .btn-remove-cart:hover {
        color: red;
    }

    .cart-qty-box {
        display: inline-flex;
        align-items: center;
        vertical-align: middle;
        background: #fff;
        border: 1px solid #D9D9D9;
        border-radius: 3px;
        overflow: hidden;
    }

    .cart-qty-btn {
        width: 28px;
        line-height: 28px;
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        color: #1F1B56;
        cursor: pointer;
        user-select: none;
    }

    .cart-qty-btn:hover {
        background: #F1F2F7;
    }

    .cart-qty-box input.cart-qty {
        width: 46px;
        padding: 3px 0;
        border: 0;
        outline: none;
        background: transparent;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .cart-qty-box input.cart-qty::-webkit-outer-spin-button,
    .cart-qty-box input.cart-qty::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
@endsection
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
            <span class="breadcrumbs-link">Giỏ hàng</span>
        </div>
    </div>
</div>
<div class="container mb-4">
    @if(session('carts'))
    @php $total = 0 @endphp
    <div class="row g-1 g-lg-4 ">
        <div class="col-lg-6">
            <form action="{{ route('frontend.payment.store') }}" method="post" class="form-cart bg-fff p-20 rounded-10" data-skip-ajax>
                @csrf
                <h2 class="text-000 fs-20 pb-10">Thông tin người nhận hàng</h2>
                <div class="mt-3">
                    <label>Họ và tên <span class="zing-required">*</span></label>
                    <input required type="text" name="customer_name" class="col-12 rounded-5 border border-D9D9D9 p-5 @error('customer_name') zing-invalid @enderror" value="{{ old('customer_name') }}" style="height: 40px">
                    @error('customer_name')
                    <span id="name-error" class="zing-required">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label>Địa chỉ <span class="zing-required">*</span></label>
                    <input required type="text" name="customer_address" class="col-12 rounded-5 border border-D9D9D9 p-5 @error('customer_address') zing-invalid @enderror" value="{{ old('customer_address') }}" style="height: 40px">
                    @error('customer_address')
                    <span id="name-error" class="zing-required">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label>Số điện thoại <span class="zing-required">*</span></label>
                    <input required type="text" name="customer_phone" class="col-12 rounded-5 border border-D9D9D9 p-5 @error('customer_phone') zing-invalid @enderror" value="{{ old('customer_phone') }}" style="height: 40px">
                    @error('customer_phone')
                    <span id="name-error" class="zing-required">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label>Email <span class="zing-required">*</span></label>
                    <input required type="text" name="customer_email" class="col-12 rounded-5 border border-D9D9D9 p-5 @error('customer_email') zing-invalid @enderror" value="{{ old('customer_email') }}" style="height: 40px">
                    @error('customer_email')
                    <span id="name-error" class="zing-required">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-3">
                    <label>Ghi chú</label>
                    <textarea rows="5"
                        class="col-12 rounded-5 border border-D9D9D9 p-5"
                        name="order_comments"></textarea>
                </div>
                <div class="text-center mt-3">
                    <button type="submit"
                        class="fs-16 cta-D80000 rounded-pill cursor-pointer px-20 py-10 rounded-30">Đặt hàng
                    </button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <div class="bg-fff p-20 rounded-10">
                <h2 class="text-000 fs-20 pb-10">Thông tin đơn hàng</h2>
                
                @php $total = 0; @endphp
                @foreach (session('carts', []) as $productId => $cart)
                    @php $total += ($cart['is_vat'] == 0) ? ($cart['regular_price'] + ($cart['regular_price'] * 0.1)) * $cart['quantity'] : $cart['regular_price'] * $cart['quantity'] @endphp
                    <div class="cart-item cart_line overflow-hidden mb-3 pb-3 border-bottom" data-id="{{ $cart['id'] }}">
                        <div class="position-relative" style="float:left">
                            <img class="cart-img rounded-10" style="width: 120px; height: 100px; display:block" src="{{ $cart['image'] }}" alt="{{ $cart['name'] }}">
                            <a href="#" class="btn-delete-cart position-absolute" data-id="{{ $cart['id'] }}" style="background-color: unset; top: 2px; right: -2px">
                                <img src="images/icon_trash.png" alt="xoa san pham" style="width: 20px; height: 20px; display:block; background-color: unset;">
                            </a>
                        </div>
                        <div class="" style="padding-left: 130px">
                            <h3 class="cart-title mb-1">
                                <a class="fs-16 text-D33 fw-5" href="{{ route('frontend.slug', ['slug' => $cart['slug']]) }}">
                                    {{ $cart['name'] }}
                                </a>
                            </h3>
                            <div>Số lượng:
                                <label class="cart-qty-box">
                                    <span class="cart-qty-btn" data-step="-1" role="button" aria-label="Giảm số lượng">&minus;</span>
                                    <input class="cart-qty text-center" type="number" min="1" max="500000" value="{{ $cart['quantity'] }}" data-id="{{ $cart['id'] }}">
                                    <span class="cart-qty-btn" data-step="1" role="button" aria-label="Tăng số lượng">+</span>
                                </label> x
                                <span class="cart_tong" data-id="{{ $cart['id'] }}">{{ number_format($cart['regular_price']) }}</span>đ<br>
                                <small class="text-secondary">({{ $cart['is_vat_label'] }})</small>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- KHUNG NHẬP MÃ GIẢM GIÁ (DÁN MÃ VÀO ĐÂY) -->
                <!-- KHUNG NHẬP MÃ GIẢM GIÁ -->
<div class="coupon-apply-box my-3 rounded-10 bg-light">
    <label for="couponCodeInput" class="form-label fw-6 fs-15 mb-2 text-000">Mã giảm giá</label>
    <div class="input-group d-flex" style="gap: 10px;">
        <input type="text" id="couponCodeInput" class="form-control border border-D9D9D9 p-2 rounded-3 flex-grow-1" placeholder="Nhập hoặc dán mã vào đây..." style="outline: none;">
        <button type="button" id="btnApplyCoupon" class="btn cta-D80000 px-4 py-2 rounded-3" style="padding: 0rem 1rem;">Áp dụng</button>
    </div>
    <!-- Thẻ hiển thị thông báo lỗi hoặc thành công -->
    <div id="couponMessage" class="mt-2 fs-14" style="display: none;"></div>
</div>
<!-- KẾT THÚC KHUNG NHẬP MÃ -->
                <!-- KẾT THÚC KHUNG NHẬP MÃ -->
                <div class="cart-total text-end fs-20 text-D33 fw-7 py-3" style="line-height: 1.3; border-top: 1px dashed #d8d8d8; border-bottom: 1px dashed #d8d8d8">
                    {{-- Hiển thị số tiền được giảm (ẩn đi nếu chưa áp mã) --}}
                    <div id="couponDiscountRow" style="display: none; font-size: 16px; font-weight: normal; color: #666;">
                        Giảm giá: -<span id="discountAmount">0</span> VNĐ
                    </div>
                    
                    {{-- Tổng tiền thanh toán cuối cùng --}}
                    <span><strong id="finalTotalAmount">{{ number_format($total ?? 0) }}</strong></span> VNĐ <br>
                    <small class="fs-14 fw-normal">(Giá đã bao gồm VAT 10%)</small>
                </div>
                {{-- <div class="cart-total text-end fs-20 text-D33 fw-7 py-3" style="line-height: 1.3; border-top: 1px dashed #d8d8d8; border-bottom: 1px dashed #d8d8d8">
                    <span>{{ number_format($total ?? 0) }}</span> VNĐ <br><small class="fs-14 fw-normal">(Giá đã bao gồm VAT 10%)</small>
                </div> --}}
                
                <div class="text-end pt-10">
                    <a href="{{ route('frontend.cart.destroy') }}" class="btn-remove-cart text-danger me-3">Xóa giỏ hàng</a>
                    <div class="clr"></div>
                    <a href="{{ $buyMoreUrl ?? route('frontend.page.home') }}" class="pt-10 d-inline-block cart-buy text-000 fw-6 text-1F1B56-hover">Mua thêm sản phẩm khác</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center p-20 bg-fff">
        <p>Bạn chưa có sản phẩm nào trong giỏ hàng. Vui lòng xem và chọn sản phẩm <a style="color: #d60118" href="{{ route('frontend.page.home') }}">tại đây</a>!</p>
    </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script type="text/javascript">
    // Nut + / - o o so luong: doi gia tri roi kich hoat .change() de doan tren goi AJAX /cart/update
    $('.cart-qty-btn').click(function() {
        var $box = $(this).closest('.cart-qty-box');
        var $input = $box.find('.cart-qty');
        var current = parseInt($input.val(), 10);
        if (isNaN(current) || current < 1) current = 1;
        var next = current + parseInt($(this).data('step'), 10);
        if (next < 1) next = 1;
        if (next > 500000) next = 500000;
        if (next === current) return;
        $input.val(next).change();
    });

    $('.cart-qty').change(function() {
        var id = $(this).attr('data-id');
        var num = $(this).val();
        $('.cart_tong[data-id=' + id + ']').text('Đang xử lý ...');
        if (isNaN(parseInt(num)) || parseInt(num) <= 0) {
            return false;
        }
        $.post('/cart/update/' + id, {
            quantity: num,
            _token: csrf
        }, function(data) {
            var carts = Object.entries(data);
            var total = 0;
            var thisTotal = 0;
            carts.forEach(function(cart) {
                let vat = Number.parseInt(cart[1].regular_price * 0.1);
                let quantity = Number.parseInt(cart[1].quantity);
                let regular_price = Number.parseInt(cart[1].regular_price);
                total += (cart[1].is_vat == 0 ? quantity * (regular_price + vat) : quantity * regular_price);
                if (cart[1].id == id) {
                    thisTotal += cart[1].regular_price;
                }
            });
            $(".txtTotal").val(total);
            total = new Intl.NumberFormat().format(total);
            thisTotal = new Intl.NumberFormat().format(thisTotal);
            $(".cart-total span").html(total);
            $('.cart_tong[data-id=' + id + ']').html(thisTotal);
            if (carts.length == 0) {
                location.reload();
            }
        });
    });
    $('.btn-delete-cart').click(function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $('.cart_tong[data-id=' + id + ']').text('Đang xử lý ...');

        $.post('/cart/remove/' + id, {
            _token: csrf
        }, function(data) {
            var carts = Object.entries(data);
            var total = 0;

            carts.forEach(function(cart) {
                let price = Number.parseInt(cart[1].regular_price);
                let quantity = Number.parseInt(cart[1].quantity);
                let vat = price * 0.1;

                total += (cart[1].is_vat == 0 ?
                    quantity * (price + vat) :
                    quantity * price);
            });

            // Đổ lại tổng tiền
            $(".txtTotal").val(total);
            $(".cart-total span").html(new Intl.NumberFormat().format(total));

            // Xóa dòng giỏ hàng
            $('.cart_line[data-id=' + id + ']').remove();

            // Cập nhật số lượng giỏ mini
            $(".cart-mini span").html(carts.length);

            // Nếu giỏ trống thì reload
            if (carts.length == 0) {
                location.reload();
            }
        });
    });
</script>

<script>
    document.getElementById('btnApplyCoupon').addEventListener('click', function () {
        let couponCode = document.getElementById('couponCodeInput').value.trim();
        let messageBox = document.getElementById('couponMessage');

        if (!couponCode) {
            messageBox.style.display = 'block';
            messageBox.style.color = 'red';
            messageBox.innerText = 'Vui lòng nhập mã giảm giá!';
            return;
        }

        // Gửi request AJAX lên Laravel
        fetch('{{ route("frontend.cart.applyCoupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: couponCode })
        })
        .then(response => response.json())
        .then(data => {
            messageBox.style.display = 'block';
            if (data.success) {
                messageBox.style.color = 'green';
                messageBox.innerText = data.message;

                // Hiển thị dòng giảm giá và cập nhật số tiền tổng cộng
                document.getElementById('couponDiscountRow').style.display = 'block';
                document.getElementById('discountAmount').innerText = data.discount_formatted;
                document.getElementById('finalTotalAmount').innerText = data.new_total_formatted;
            } else {
                messageBox.style.color = 'red';
                messageBox.innerText = data.message; // Hiển thị chi tiết lỗi (chưa đủ điều kiện, sai sản phẩm,...)
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageBox.style.display = 'block';
            messageBox.style.color = 'red';
            messageBox.innerText = 'Đã xảy ra lỗi kết nối hệ thống!';
        });
    });
</script>
@endpush

@stop

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/cart.min.css?v=' . filemtime(public_path('css-new/dist/cart.min.css'))) }}">
@endpush
