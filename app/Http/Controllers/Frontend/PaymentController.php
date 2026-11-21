<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\OrderStatus;
use App\Helpers\AgentHelper;
use App\Http\Controllers\FrontendController;
use App\Http\Requests\OrderRequest;
use App\Mail\RegisterOrder;
use App\Models\New_Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Mail;

class PaymentController extends FrontendController
{
    public function index()
    {
        try {
            $carts = session()->get('carts', []);

            if (count($carts) <= 0) {
                return redirect()->route('frontend.cart.index');
            }

            $categories = $this->getCategories();
            $settings = $this->getSettings();
        } catch (Exception $e) {
            Log::error(PaymentController::class . '->' . 'index :' . $e->getMessage());
            return redirect()->route('frontend.page.home');
        }

        return view('frontend.payment.index', [
            'categories' => $categories,
            'settings' => $settings,
            'customer' => Auth::guard('customers')->user(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\OrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(OrderRequest $request)
    // {
    //     try {
    //         $carts = session()->get('carts', []);
    //         $customer_id = null;

    //         if (Auth::guard('customers')->check()) {
    //             $customer_id = Auth::guard('customers')->user()->id;
    //         }

    //         $total = 0;

    //         foreach ($carts as $cart) {
    //             if ($cart['is_vat'] == 0) {
    //                 $itemTotal = ($cart['regular_price'] + ($cart['regular_price'] * 0.1)) * $cart['quantity'];
    //             } else {
    //                 $itemTotal = $cart['regular_price'] * $cart['quantity'];
    //             }
    //             $total += $itemTotal;
    //         }

    //         $order = Order::create([
    //             'customer_name' => $request->customer_name,
    //             'customer_email' => $request->customer_email,
    //             'customer_address' => $request->customer_address,
    //             'customer_phone' => $request->customer_phone,
    //             'customer_note' => $request->order_comments,
    //             'customer_id' => $customer_id,
    //             'status' => OrderStatus::PROCESSING->value,
    //             'total' => $total,
    //         ]);

    //         foreach ($carts as $id => $cart) {
    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'product_id' => $id,
    //                 'product_name' => $cart['name'],
    //                 'is_vat' => $cart['is_vat'],
    //                 'quantity' => $cart['quantity'],
    //                 'price' => $cart['regular_price'],
    //             ]);
    //             New_Product::where('id', $id)->increment('sold_quantity', $cart['quantity']);
    //         }

    //         Mail::to($request->customer_email)->send(new RegisterOrder($order));
    //     } catch (Exception $e) {
    //         Log::error(PaymentController::class . '->' . 'store :' . $e->getMessage());
    //         return redirect()->route('frontend.page.home');
    //     }

    //     session()->forget('carts');

    //     return redirect()->route('frontend.payment.success', [
    //         'id' => Crypt::encrypt($order->id)
    //     ]);
    // }
    public function store(OrderRequest $request)
    {
        try {
            $carts = session()->get('carts', []);
            
            // Kiểm tra nếu giỏ hàng trống thì quay về trang giỏ hàng ngay
            if (empty($carts)) {
                return redirect()->route('frontend.cart.index');
            }

            $customer_id = Auth::guard('customers')->id();

            $total = 0;

            foreach ($carts as $cart) {
                // Đảm bảo lấy đúng key giá (ở trang giỏ hàng bạn dùng regular_price)
                $price = $cart['regular_price'] ?? 0;
                $qty = $cart['quantity'] ?? 1;
                $isVat = $cart['is_vat'] ?? 1;

                if ($isVat == 0) {
                    $itemTotal = ($price + ($price * 0.1)) * $qty;
                } else {
                    $itemTotal = $price * $qty;
                }
                $total += $itemTotal;
            }

            // Lấy tiền giảm giá từ session
            // Lấy thông tin mã giảm giá từ session
            $couponCode = null;
            $discount = 0;
            if (session()->has('applied_coupon')) {
                $couponCode = session('applied_coupon')['code'] ?? null; // Tên key mã giảm giá trong session của bạn
                $discount = session('applied_coupon')['discount'] ?? 0;
            }

            $finalTotal = max(0, $total - $discount);

            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'customer_phone' => $request->customer_phone,
                'customer_note' => $request->order_comments,
                'customer_id' => $customer_id,
                'status' => OrderStatus::PROCESSING->value,
                'total' => $finalTotal, 
                'coupon_code' => $couponCode,         // Lưu mã giảm giá
                'discount_amount' => $discount,      // Lưu tiền giảm giá
            ]);

            foreach ($carts as $id => $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $id,
                    'product_name' => $cart['name'],
                    'is_vat' => $cart['is_vat'] ?? 1,
                    'quantity' => $cart['quantity'] ?? 1,
                    'price' => $cart['regular_price'] ?? 0,
                ]);
                New_Product::where('id', $id)->increment('sold_quantity', $cart['quantity'] ?? 1);
            }

            Mail::to($request->customer_email)->send(new RegisterOrder($order));
        } catch (Exception $e) {
            Log::error(PaymentController::class . '->' . 'store :' . $e->getMessage());
            return redirect()->route('frontend.page.home');
        }

        // Xóa giỏ hàng và mã giảm giá sau khi đặt hàng thành công
        session()->forget(['carts', 'applied_coupon']);

        return redirect()->route('frontend.payment.success', [
            'id' => Crypt::encrypt($order->id)
        ]);
    }

    public function success($id)
    {
        try {
            $orderId = Crypt::decrypt($id);
            $categories = $this->getCategories();
            $orderItems = OrderItem::selectRaw('m_order_items.*, m_products.slug')
                ->leftJoin('m_products', 'm_order_items.product_id', '=', 'm_products.id')
                ->where('m_order_items.order_id', $orderId)
                ->get();
            $order = Order::find($orderId);
            $settings = $this->getSettings();
        } catch (Exception $e) {
            Log::error(PaymentController::class . '->' . 'success :' . $e->getMessage());
            return redirect()->route('frontend.page.home');
        }

        return view('web.payment.success', [
            'categories' => $categories,
            'orderItems' => $orderItems,
            'order' => $order,
            'settings' => $settings,
        ]);
    }
}
