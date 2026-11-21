<?php
namespace App\Services\FeServices;

use App\DTOs\CommentDto;
use App\Http\Requests\Fe\CommentRequest;
use App\Models\Product;
use App\Models\Comment;
use App\Models\New_Product;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class CommentService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Comment::class));
        // Cập nhật lại selectList cho khớp cột mới nếu cần (thêm product_slug)
        $this->selectList = ['product_slug', 'author', 'rating', 'content', 'created_at'];
    }

    /**
     * Toàn bộ comment sản phẩm. Khoá theo product_id để đánh giá đi theo sản phẩm
     * khi admin đổi slug; nhánh product_slug giữ lại các row cũ chưa có product_id.
     */
    private function productComments(New_Product $product): Builder
    {
        return Comment::where('type', 'product')
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id)
                    ->orWhere(function ($or) use ($product) {
                        $or->whereNull('product_id')->where('product_slug', $product->slug);
                    });
            });
    }

    public function getByProduct(New_Product $product, int $limit = 5): array
    {
        $data = $this->productComments($product)
            ->where('status', 'yes')
            ->whereNotNull('rating')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
        return $data->toArray();
    }

    /**
     * Hỏi - đáp: câu hỏi gốc (không có rating) kèm các trả lời của quản trị viên.
     */
    public function getQuestionThreadsByProduct(New_Product $product, int $limit = 4): array
    {
        $questions = $this->productComments($product)
            ->where('status', 'yes')
            ->whereNull('rating')
            ->whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return $questions->map(function ($question) {
            $thread = $question->toArray();
            $thread['replies'] = Comment::where('parent_id', $question->id)
                ->where('status', 'yes')
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
            return $thread;
        })->toArray();
    }

    /**
     * Hỏi - đáp của bài viết: câu hỏi gốc (không rating, không parent) kèm các trả lời.
     */
    public function getQuestionThreadsByPost(Post $post, int $limit = 8): array
    {
        $questions = Comment::where('post_id', $post->id)
            ->where('type', 'post')
            ->where('status', 'yes')
            ->whereNull('rating')
            ->whereNull('parent_id')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        return $questions->map(function ($question) {
            $thread = $question->toArray();
            $thread['replies'] = Comment::where('parent_id', $question->id)
                ->where('status', 'yes')
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
            return $thread;
        })->toArray();
    }

    public function getReviewStatsByProduct(New_Product $product): array    {
        $result = $this->productComments($product)
            ->where('status', 'yes')
            ->whereNotNull('rating')
            ->selectRaw('AVG(rating) as avg_vote, COUNT(id) as total_vote')
            ->first();

        return [
            'avg'   => $result && $result->avg_vote ? round($result->avg_vote, 1) : 0,
            'total' => $result ? (int) $result->total_vote : 0,
        ];
    }

    public function getVoteDistributionByProduct(New_Product $product): array
    {
        $distribution = $this->productComments($product)
            ->where('status', 'yes')
            ->whereNotNull('rating')
            ->selectRaw('rating, COUNT(id) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }

    public static function create(New_Product $product, CommentRequest $request)
    {
        $data = $request->validated();
        $data['product_slug'] = $product->slug;
        $data['type'] = 'product';
        
        Comment::create($data);
    }
}