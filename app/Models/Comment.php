<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\CommentType;

class Comment extends BaseModel
{
    use IsActiveTrait;
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $table = 'm_comments';

    protected $fillable = [
        'product_id',
        'product_slug',
        'author',
        'author_email',
        'content',
        'rating',
        'status',
        'type',
        'parent_id',
        'post_id',
        'user_id',
    ];

    protected $casts = [
        'type' => CommentType::class,
        'status' => CommentStatus::class,
    ];

    public function productInfo()
    {
        return $this->hasOne(Product::class, 'slug', 'product_slug');
    }

    public function postInfo()
    {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }

    public function childs() {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    public function getStatusName()
    {
        switch ($this->status->value) {
            case CommentStatus::YES->value:
                $className = 'success';
                break;
            default:
                $className = 'danger';
                break;
        }

        return '<span class="badge bg-' . $className . '">' . $this->status->label() . '</span>';
    }
}
