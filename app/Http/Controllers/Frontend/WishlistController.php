<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\AgentHelper;
use App\Http\Controllers\FrontendController;
use App\Models\Product;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class WishlistController extends BaseController
{

    public function add(Request $request)
    {
        $id = $request->get('id');

        if (!$id) {
            return response()->json(['status' => false, 'message' => 'Thiếu ID sản phẩm']);
        }

        $wishlist = session()->get('wishlist', []);

        if (in_array($id, $wishlist)) {
            $wishlist = array_diff($wishlist, [$id]);
            session()->put('wishlist', $wishlist);

            return response()->json([
                'status' => true,
                'action' => 'removed',
                'message' => 'Đã xóa khỏi yêu thích',
                'total' => count($wishlist),
            ]);
        }

        $wishlist[] = $id;
        session()->put('wishlist', $wishlist);

        return response()->json([
            'status' => true,
            'action' => 'added',
            'message' => 'Đã thêm vào yêu thích',
            'total' => count($wishlist),
        ]);
    }
}
