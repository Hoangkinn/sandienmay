<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CommentStatus;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::orderByDesc('created_at')->where('is_deleted', 0)->limit(10)->get();
        $products = Product::orderByDesc('created_at')
            ->where('status', Status::ACTIVED->value)
            ->limit(5)
            ->get();
        $comments = Comment::where('status', CommentStatus::NO->value)->orderByDesc('created_at')->limit(10)->get();

        return view('backend.dashboard', [
            'orders' => $orders,
            'products' => $products,
            'comments' => $comments,
        ]);
    }
}
