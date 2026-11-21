<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerOrderController extends FrontendController
{
    private function customerId(): int
    {
        return (int) Auth::guard('customers')->id();
    }

    public function index(): View
    {
        $orders = Order::with('items')
            ->where('customer_id', $this->customerId())
            ->where('is_deleted', 0)
            ->latest('id')
            ->paginate(10);

        return view('web.customer.orders', [
            'orders' => $orders,
            'customer' => Auth::guard('customers')->user(),
        ]);
    }

    public function show(Order $order): View
    {
        // Route binding lấy đơn theo id toàn DB -> bắt buộc chặn sở hữu ở đây, nếu không
        // khách chỉ cần đổi số trong URL là đọc được đơn người khác.
        abort_unless((int) $order->customer_id === $this->customerId(), 404);

        $orderItems = $this->items($order->id);

        return view('web.customer.order', [
            'order' => $order,
            'orderItems' => $orderItems,
            'customer' => Auth::guard('customers')->user(),
        ]);
    }

    private function items(int $orderId)
    {
        return OrderItem::selectRaw('m_order_items.*, m_products.slug')
            ->leftJoin('m_products', 'm_order_items.product_id', '=', 'm_products.id')
            ->where('m_order_items.order_id', $orderId)
            ->get();
    }
}
