<?php

namespace App\Services\FeServices;

use App\DTOs\Post\PostListDto;
use App\Enums\CategoryType;
use App\Enums\PostType;
use App\Enums\Status;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PostService extends BaseService
{
    public function __construct()
    {
        $cateModel = resolve(Post::class);
        $this->selectList = [
            $cateModel->getTable() . '.id',
            'title',
            'type',
            'slug',
            'image',
            'order',
            'seo_no_index',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'seo_url_canonical',
            'content',
            'count_view',
            'created_at'
        ];
        parent::__construct($cateModel);
    }

    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList($builder)->where('status', Status::ACTIVED)
            ->orderBy('order');
    }

    public function getPosts($limit = 8): array
    {
        $keyCache = "getLastPostV2." . $limit;
        return Cache::remember($keyCache, 600,  function () use ($limit) {
            $items = $this->newQuery()->where('type', PostType::POST)
                ->hasThumb()
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
            return PostListDto::from($items);
        });
    }

    public function detail($slug)
    {
        $detail = $this->getList()->with('author')->where('type', PostType::POST)->where('slug', $slug)->first();
        return $detail;
    }

    public function getLastPost($slug)
    {
        $keyCache = "getLastPostV2";
        return Cache::remember($keyCache, 600,  function () use ($slug) {
            $items = $this->newQuery()->where('type', PostType::POST)->where('slug', '!=', $slug)
                ->hasThumb()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
            return PostListDto::from($items);
        });
    }

    public function getRelatedPost($slug)
    {
        $keyCache = "related_posts_v6_" . $slug;

        return Cache::remember($keyCache, 600, function () use ($slug) {
            $currentPost = $this->newQuery()
                ->where('type', PostType::POST)
                ->where('slug', $slug)
                ->first();

            if (!$currentPost) {
                return collect();
            }

            $categoryIds = $currentPost->categories()->pluck('m_categories.id');

            if ($categoryIds->isEmpty()) {
                return collect();
            }

            // Thêm m_categories.id để tránh xung đột tên cột trong câu lệnh subquery
            $specificCategoryIds = $currentPost->categories()
                ->whereNotIn('m_categories.id', function ($query) use ($categoryIds) {
                    $query->select('parent_id')
                        ->from('m_categories')
                        ->whereNotNull('parent_id')
                        ->whereIn('parent_id', $categoryIds);
                })
                ->pluck('m_categories.id');

            $targetCategoryIds = $specificCategoryIds->isNotEmpty() ? $specificCategoryIds : $categoryIds;

            $items = $this->newQuery()
                ->where('type', PostType::POST)
                ->hasThumb()
                ->where('id', '!=', $currentPost->id)
                ->whereHas('categories', function ($q) use ($targetCategoryIds) {
                    $q->whereIn('m_categories.id', $targetCategoryIds);
                })
                ->orderByDesc('created_at')
                ->limit(7)
                ->get();

            return $items->map(function ($item) {
                return new PostListDto($item);
            });
        });
    }
    public function search($keyword, $limit = 10)
    {
        return $this->getList()
            ->where('type', PostType::POST)
            ->hasThumb()
            ->whereRaw("LOWER(REPLACE(title, '-', ' ')) LIKE ?", ['%' . strtolower(str_replace('-', ' ', $keyword)) . '%'])
            ->limit($limit)
            ->get();
    }
}
