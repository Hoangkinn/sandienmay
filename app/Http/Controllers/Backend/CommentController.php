<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CommentStatus;
use App\Enums\CommentType;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $comments = Comment::where('parent_id', null)->orderByDesc('created_at');

        if ($request->type) {
            $comments = $comments->where('type', $request->type);
        }

        if ($request->approved) {
            $comments = $comments->where('status', $request->approved);
        }

        if ($request->q) {
            $comments = $comments->where('content', 'like', '%'. $request->q .'%');
        }

        // Bình luận sản phẩm không có rating là câu hỏi trong khối Hỏi và đáp
        if ($request->kind == 'review') {
            $comments = $comments->whereNotNull('rating');
        } elseif ($request->kind == 'qa') {
            $comments = $comments->whereNull('rating')
                ->where('type', CommentType::PRODUCT->value);
        }

        $comments = $comments->paginate(10);

        $userLogin = Auth::user();
        $statusList = CommentStatus::cases();
        return view('backend.comment.index', [
            'comments' => $comments,
            'userLogin' => $userLogin,
            'statusList' => $statusList,
            'type' => $request->type,
            'currentStatus' => $request->approved,
            'kind' => $request->kind,
            'q' => $request->q,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        $comment->content = $request->content;
        $comment->save();
        
        // Xóa cache thống kê của sản phẩm khi cập nhật nội dung
        Cache::forget('product-stat-review-' . $comment->product_id);

        session()->flash('backend.toast.success', 'Lưu thông tin comment thành công!');
        return [
            "content" => $request->content,
            "error" => false,
            "message" => "Lưu thành công"
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $commentParent = Comment::where('parent_id', $id)->first();
        if ($commentParent) {
            session()->flash('backend.toast.error', 'Tồn tại comment reply không thể xóa!');
            return redirect()->route('backend.comment.index');
        }

        $comment = Comment::find($id);
        if ($comment) {
            $productId = $comment->product_id;
            $comment->delete();
            
            // Xóa cache thống kê của sản phẩm sau khi xóa comment thành công
            Cache::forget('product-stat-review-' . $productId);
        }

        session()->flash('backend.toast.success', 'Xóa comment thành công!');
        return redirect()->route('backend.comment.index');
    }

    /**
     * Reply the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $id)
    {
        $comment = Comment::find($id);

        $data = [
            'author' => $request->author,
            'author_email' => $request->author_email,
            'content' => $request->content,
            'type' => $comment->type->value,
            'parent_id' => $id,
            'user_id' => Auth::user()->id,
        ];

        if ($comment->type == CommentType::PRODUCT->value) {
            $data['product_id'] = $comment->product_id;
        }

        if ($comment->type == CommentType::POST->value) {
            $data['post_id'] = $comment->post_id;
        }

        $commentInfo = Comment::create($data);

        return [
            "error" => false,
            "message" => "Lưu thành công",
            "parent" => $id,
            "row" => '<tr class="row-' . $commentInfo->id . '">
                        <td>' . $commentInfo->id . '</td>
                        <td>
                            <b class="d-block">' . $commentInfo->author . '</b>
                            <p class="mb-0">' . $commentInfo->author_email . '</p>
                            <p class="mb-0 text-nowrap">' . $commentInfo->created_at . '</p>
                            <p class="mb-0">Phê duyệt: <span class="badge badge-danger">No</span></p>
                        </td>
                        <td>
                            <p class="comment-content-' . $commentInfo->id . '">' . $commentInfo->content . '</p>
                            <div class="form-edit-' . $commentInfo->id . '" data-route="/backend/ajax/comments/' . $commentInfo->id . '/edit"></div>
                        </td>
                        <td>
                            <div class="table-row-action">
                                <a class="bg-warning pl-1 pr-1" href="javascript:approved(' . $commentInfo->id . ');" target="_self">Duyệt hiển thị</a>
                                <br>
                                <a class="bg-primary pl-1 pr-1" href="javascript:loadFormEdit(' . $commentInfo->id . ')" target="_self">Sửa</a>
                                <br>
                                <a class="bg-danger pl-1 pr-1 table-btn-delete" href="/backend/comments/' . $commentInfo->id . '" target="_self">Xóa</a>
                            </div>
                        </td>
                    </tr>'
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approved($id, $status)
    {
        $commentInfo = Comment::find($id);
        $commentInfo->status = $status;
        $productLink = '';
        if ($commentInfo->productInfo) {
            $url = route('frontend.slug', ['slug' => $commentInfo->productInfo->slug]);
            $productName = $commentInfo->productInfo->name;
            $productLink = "<p>Sản phẩm: <a href=\"{$url}\" target=\"_blank\">{$productName}</a></p>";
        }

        // Khởi tạo giá trị mặc định là chuỗi rỗng để tránh lỗi Undefined variable khi không có sao
        $rating = '';
        if ($commentInfo->rating){
           $rating = "<p class=\"mb-0\">Đánh giá: <span class=\"badge badge-success\"> $commentInfo->rating </span> ⭐</p>";
        }
                                                    
        $commentInfo->save();

        // Xóa cache thống kê của sản phẩm khi trạng thái duyệt thay đổi (Hiển thị / Ẩn)
        Cache::forget('product-stat-review-' . $commentInfo->product_id);

        $commentStatus = ($status == CommentStatus::YES->value) ? CommentStatus::NO->value : CommentStatus::YES->value;
        $statusLabel = ($status == CommentStatus::YES->value) ? 'Không duyệt' : 'Duyệt hiển thị';

        session()->flash('backend.toast.success', 'Lưu thành công');
        return [
            "error" => false,
            "message" => "Lưu thành công",
            "row" => '<tr id="row-' . $commentInfo->id . '">
                <td>' . $commentInfo->id . '</td>
                <td>
                    <b class="d-block">' . $commentInfo->author . '</b>
                    <p class="mb-0">' . $commentInfo->author_email . '</p>
                    <p class="mb-0 text-nowrap">' . $commentInfo->created_at . '</p>
                    <p class="mb-0">Phê duyệt: '. $commentInfo->getStatusName(). '</p>
                    ' . $rating . '
                </td>
                <td>
                     ' . $productLink . '
                    <p class="comment-content-' . $commentInfo->id . '">' . $commentInfo->content . '</p>
                    <div class="form-edit-' . $commentInfo->id . '" data-route="/backend/ajax/comments/' . $commentInfo->id . '/edit"></div>
                    </td>
                <td>
                    <div class="table-row-action">
                        <a class="bg-warning pl-1 pr-1" href="javascript:approved(' . $commentInfo->id . ', `'. $commentStatus .'`);" target="_self">'. $statusLabel .'</a>
                        <br>
                        <a class="bg-danger pl-1 pr-1 table-btn-delete" href="/backend/comments/' . $commentInfo->id . '" target="_self">Xóa</a>
                    </div>
                </td>
            </tr>'
        ];
    }
}