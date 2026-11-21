<?php

namespace App\Http\Controllers\Backend;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::orderByDesc('created_at')
            ->where('is_deleted', 0)
            ->paginate(10);

        return view('backend.order.index', [
            'orders' => $orders
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);
        $statusList = OrderStatus::cases();

        return view('backend.order.show', [
            'order' => $order,
            'statusList' => $statusList,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if ($order->status->value == OrderStatus::COMPLETED->value) {
            session()->flash('backend.toast.error', 'Không thể xóa hóa đơn!');
            return redirect()->route('backend.order.index');
        }

        $order->is_deleted = 1;
        $order->save();

        session()->flash('backend.toast.success', 'Xóa hóa đơn thành công!');
        return redirect()->route('backend.order.index');
    }

    /**
     * Print order detail.
     *
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        $order = Order::find($id);

        return view('backend.order.print', [
            'order' => $order
        ]);
    }

    /**
     * Update status.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        session()->flash('backend.toast.success', 'Cập nhật đơn hàng thành công!');
        return redirect()->route('backend.order.index');
    }
}
