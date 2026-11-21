<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Post extends BaseModel
{
    protected $table = 'm_posts';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'image',
        'content',
        'order',
        'status',
        'type',
        'seo_no_index',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'seo_url_canonical',
        'schema',
        'user_id',
        'count_view',
        'is_featured',
    ];

    // Bài chưa có ảnh đại diện thì không xuất hiện ở bất kỳ block danh sách tin tức nào;
    // qualifyColumn để an toàn khi query join bảng pivot danh mục.
    public function scopeHasThumb(Builder $query): Builder
    {
        $image = $query->getModel()->qualifyColumn('image');
        return $query->whereNotNull($image)->where($image, '<>', '');
    }

    protected static function boot()
    {
        parent::boot();

        // Xóa sạch tất cả cache phân trang khi Đăng / Sửa / Xóa bài viết
        static::saved(function ($post) {
            self::clearPostIndexCache();
        });

        static::deleted(function ($post) {
            self::clearPostIndexCache();
        });
    }

    public static function clearPostIndexCache()
    {
        // 1. Dành cho File Driver: Tìm và xóa tất cả file cache phân trang trong thư mục storage
        $storagePath = storage_path('framework/cache/data');
        if (file_exists($storagePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $contents = @file_get_contents($fileinfo->getRealPath());
                    if ($contents && str_contains($contents, 'frontend_posts_index_page_')) {
                        @unlink($fileinfo->getRealPath());
                    }
                }
            }
        }

        // 2. Dự phòng: Xóa key trực tiếp tới 100 trang phân trang
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget('frontend_posts_index_page_' . $i);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'm_category_post', 'post_id', 'category_id');
    }

    public function author()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'm_category_post', 'category_id', 'post_id');
    }
}