<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\CommentType;
use App\Http\Controllers\FrontendController;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Models\Comment;
use App\Services\FeServices\CommentService;
use App\Services\FeServices\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function save(StoreCommentRequest $request, $id, $type)
    // {
    //     $data = [
    //         'author' => $request->author,
    //         'author_email' => $request->author_email,
    //         'content' => $request->content,
    //         'rating' => $request->rating,
    //         'type' => $type,
    //         'parent_id' => $request->parent_id,
    //     ];

    //     if (Auth::user()) {
    //         $data['user_id'] = Auth::user()->id;
    //     }

    //     // CHUẨN: Lấy trực tiếp biến $id nhận từ URL Route
    //     if ($type == CommentType::PRODUCT->value) {
    //         $data['product_id'] = $id; 
    //     }

    //     // CHUẨN: Lấy trực tiếp biến $id nhận từ URL Route
    //     if ($type == CommentType::POST->value) {
    //         $data['post_id'] = $id; 
    //     }

    //     Comment::create($data);

    //     // Xóa bớt cache để đánh giá mới được hiển thị ngay lập tức ngoài giao diện
    //     \Illuminate\Support\Facades\Cache::forget("comment-product-$id");
    //     \Illuminate\Support\Facades\Cache::forget("product-stat-review-$id");

    //     return [
    //         "error" => false,
    //         "item" => "",
    //         "message" => "Cảm ơn bạn đã để lại ý kiến.",
    //         "wrapper" => "comment-product"
    //     ];
    // }
    public function save(StoreCommentRequest $request, $slugOrId, $type)
    {
        $data = [
            'author' => $request->author,
            'author_email' => $request->author_email,
            'content' => $request->content,
            'rating' => $request->rating,
            'type' => $type,
            'parent_id' => $request->parent_id,
        ];

        if (Auth::user()) {
            $data['user_id'] = Auth::user()->id;
        }

        if ($type == CommentType::PRODUCT->value) {
            // Gắn thêm product_id để toàn bộ đánh giá / hỏi đáp đi theo sản phẩm,
            // không bị mồ côi khi admin sửa lại "Đường dẫn" (slug).
            $product = \App\Models\New_Product::where('slug', $slugOrId)->first();
            if (!$product) {
                return response()->json([
                    'error' => true,
                    'message' => 'Sản phẩm không tồn tại',
                    'errors' => ['product' => ['Sản phẩm không tồn tại']],
                ], 422);
            }

            $data['product_id'] = $product->id;
            $data['product_slug'] = $product->slug;
        }

        if ($type == CommentType::POST->value) {
            $data['post_id'] = $slugOrId; 
        }

        Comment::create($data);

        return [
            "error" => false,
            "item" => "",
            "message" => "Cảm ơn bạn đã để lại ý kiến.",
            "wrapper" => "comment-product"
        ];
    }

    public function getRated(string $slugOrId): JsonResponse
    {
        $product = \App\Models\New_Product::where('slug', $slugOrId)->first();
        if (!$product) {
            // Trả JSON chứ không abort(404) để e.json() phía JS không nhận phải trang HTML
            return response()->json(array_fill_keys(range(1, 5), 0), 404);
        }

        return $this->returnJson(resolve(CommentService::class)->getVoteDistributionByProduct($product));
    }
}
