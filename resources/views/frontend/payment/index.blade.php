@extends('layouts.frontend')

@section('title', $settings[2]['setting_value'])
@section('description', $settings[4]['setting_value'])
@section('keywords', $settings[3]['setting_value'])

@section('content')
<div id="content" class="content-area page-wrapper" role="main">
    <div class="row row-main">
        <div class="large-12 col">
            <div class="col-inner">
                <div class="woocommerce">
                    <div class="woocommerce-notices-wrapper"></div>
                    <div class="woocommerce-form-coupon-toggle">
                        <div class="woocommerce-info message-wrapper">
                            <div class="message-container container medium-text-center">
                                Bạn có mã ưu đãi? <a href="#" class="showcoupon">Ấn vào đây để nhập mã</a>
                            </div>
                        </div>
                    </div>
                    <form class="checkout_coupon woocommerce-form-coupon has-border is-dashed" method="post" style="display:none">
                        <p>Nếu bạn có mã giảm giá, vui lòng điền vào phía bên dưới.</p>
                        <div class="coupon">
                            <div class="flex-row medium-flex-wrap">
                                <div class="flex-col flex-grow">
                                    <input type="text" name="coupon_code" class="input-text" placeholder="Mã ưu đãi" id="coupon_code" value="">
                                </div>
                                <div class="flex-col">
                                    <button type="submit" class="button expand" name="apply_coupon" value="Áp dụng">Áp dụng</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="woocommerce-notices-wrapper"></div>
                    <form method="POST" action="{{ route('frontend.payment.store') }}" class="checkout woocommerce-checkout" enctype="multipart/form-data">
                        @csrf
                        <div class="row pt-0 ">
                            <div class="large-7 col">
                                <div id="customer_details">
                                    <div class="clear">
                                        <div class="woocommerce-billing-fields">
                                            <h3>Thông tin thanh toán</h3>
                                            @if ($customer)
                                                <p class="form-row mb-2">
                                                    <span class="text-[#26348B]">
                                                        Bạn đang đăng nhập với email {{ $customer->email }} — đơn hàng này sẽ được lưu vào
                                                        <a href="{{ route('frontend.customer.orders') }}">tài khoản của bạn</a>.
                                                    </span>
                                                </p>
                                            @endif
                                            <div class="woocommerce-billing-fields__field-wrapper">
                                                <p class="form-row @error('customer_name') woocommerce-invalid @enderror" id="name_field">
                                                    <label for="billing_name">Họ và tên&nbsp;<abbr class="required" title="bắt buộc">*</abbr></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="text" class="input-text" name="customer_name" id="billing_name" placeholder="Nhập đầy đủ họ và tên" value="{{ old('customer_name', $customer->name ?? '') }}">
                                                    </span>
                                                    @error('customer_name')
                                                    <span id="name-error" class="alert-color invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </p>
                                                <p class="form-row" id="billing_company_field">
                                                    <label for="billing_company">Tên công ty&nbsp;<span class="optional">(tuỳ chọn)</span></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="text" class="input-text" name="billing_company" id="billing_company" value="">
                                                    </span>
                                                </p>
                                                <p class="form-row update_totals_on_change thwcfd-field-country" id="billing_country_field">
                                                    <label for="billing_country">Quốc gia&nbsp;<abbr class="required" title="bắt buộc">*</abbr></label>
                                                    <span class="woocommerce-input-wrapper"><strong>Việt Nam</strong>
                                                        <input type="hidden" name="billing_country" id="billing_country" value="VN" class="country_to_state">
                                                    </span>
                                                </p>
                                                <p class="form-row form-row-first @error('customer_address') woocommerce-invalid @enderror" id="billing_address_1_field">
                                                    <label for="billing_address_1">Địa chỉ&nbsp;<abbr class="required" title="bắt buộc">*</abbr></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="text" class="input-text" name="customer_address" id="billing_address_1" placeholder="Địa chỉ" value="{{ old('customer_address', $customer->address ?? '') }}">
                                                    </span>
                                                    @error('customer_address')
                                                    <span id="address-error" class="alert-color invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </p>
                                                <p class="form-row validate-phone @error('customer_phone') woocommerce-invalid @enderror" id="billing_phone_field">
                                                    <label for="billing_phone">Số điện thoại&nbsp;<abbr class="required" title="bắt buộc">*</abbr></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="tel" class="input-text" name="customer_phone" id="billing_phone" value="{{ old('customer_phone', $customer->phone_number ?? '') }}">
                                                    </span>
                                                    @error('customer_phone')
                                                    <span id="phone-error" class="alert-color invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </p>
                                                <p class="form-row validate-email @error('customer_email') woocommerce-invalid @enderror" id="billing_email_field">
                                                    <label for="billing_email">Địa chỉ email&nbsp;<abbr class="required" title="bắt buộc">*</abbr></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="email" class="input-text" name="customer_email" id="billing_email" value="{{ old('customer_email', $customer->email ?? '') }}">
                                                    </span>
                                                    @error('customer_email')
                                                    <span id="email-error" class="alert-color invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </p>
                                                <p class="form-row" id="billing_first_name_field">
                                                    <span class="woocommerce-input-wrapper">
                                                        <input type="text" class="input-text" name="billing_first_name" id="billing_first_name" value="">
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear">
                                        <div class="woocommerce-additional-fields">
                                            <div class="woocommerce-additional-fields__field-wrapper">
                                                <p class="form-row notesarea" id="order_comments_field">
                                                    <label for="order_comments">Ghi chú đơn hàng&nbsp;<span class="optional">(tuỳ chọn)</span></label>
                                                    <span class="woocommerce-input-wrapper">
                                                        <textarea name="order_comments" class="input-text" id="order_comments" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn." rows="2" cols="5"></textarea>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="large-5 col">
                                <div class="col-inner has-border">
                                    <div class="checkout-sidebar sm-touch-scroll">
                                        <h3 id="order_review_heading">Đơn hàng của bạn</h3>
                                        <div id="order_review" class="woocommerce-checkout-review-order">
                                            <table class="shop_table woocommerce-checkout-review-order-table">
                                                <thead>
                                                    <tr>
                                                        <th class="product-name">Sản phẩm</th>
                                                        <th class="product-total">Tạm tính</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $total = 0 @endphp
                                                    @foreach (session('carts') as $productId => $cart)
                                                    @php $total += $cart['regular_price'] * $cart['quantity'] @endphp
                                                    <tr class="cart_item">
                                                        <td class="product-name">{{ $cart['name'] }}&nbsp;
                                                            <strong class="product-quantity">×&nbsp;{{ $cart['quantity'] }}</strong>
                                                        </td>
                                                        <td class="product-total">
                                                            <span class="woocommerce-Price-amount amount">
                                                                <bdi>{{ number_format($cart['regular_price']) }} &nbsp;<span>₫</span></bdi>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="cart-subtotal">
                                                        <th>Tạm tính</th>
                                                        <td>
                                                            <span class="woocommerce-Price-amount amount">
                                                                <bdi>{{ number_format($total) }}&nbsp;<span>₫</span></bdi>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr class="woocommerce-shipping-totals shipping ">
                                                        <td class="shipping__inner" colspan="2">
                                                            <table class="shipping__table ">
                                                                <tbody>
                                                                    <tr>
                                                                        <th>Giao hàng</th>
                                                                        <td data-title="Giao hàng">
                                                                            <ul id="shipping_method" class="shipping__list woocommerce-shipping-methods">
                                                                                <li class="shipping__list_item">
                                                                                    <input type="hidden" name="shipping_method[0]" id="shipping_method_0_free_shipping15" value="free_shipping:15" class="shipping_method">
                                                                                    <label class="shipping__list_label" for="shipping_method_0_free_shipping15">Giao hàng Toàn Quốc, nhận hàng được kiểm hàng</label>
                                                                                </li>
                                                                            </ul>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr class="order-total">
                                                        <th>Tổng</th>
                                                        <td>
                                                            <strong>
                                                                <span class="woocommerce-Price-amount amount">
                                                                    <bdi>{{ number_format($total) }}&nbsp;<span>₫</span></bdi>
                                                                </span>
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            <div id="payment" class="woocommerce-checkout-payment">
                                                <ul class="wc_payment_methods payment_methods methods">
                                                    <li class="wc_payment_method payment_method_cod">
                                                        <input id="payment_method_cod" type="radio" class="input-radio" name="payment_method" value="cod" checked="checked" style="display: none;">
                                                        <label for="payment_method_cod">Trả tiền mặt khi nhận hàng (COD) </label>
                                                        <div class="payment_box payment_method_cod">
                                                            <p>Trả tiền mặt khi giao hàng.</p>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="form-row place-order">
                                                    <input type="hidden" name="total" value="{{ $total }}">
                                                    <button type="submit" class="button alt" id="place_order" value="Đặt hàng">Đặt hàng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop