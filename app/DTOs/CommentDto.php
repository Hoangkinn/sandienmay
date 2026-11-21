<?php

namespace App\DTOs;

use App\Models\Comment;
use Illuminate\Support\Carbon;

class CommentDto extends BaseDto
{
    public string $name;
    public string $rating;
    public string $content;
    public string $createdAt;
    public function __construct(Comment $item)
    {
        $this->name = $item->author ?? '';
        $this->rating = $item->rating;
        $this->content = $item->content;
        $this->createdAt = $item->created_at ?  $item->created_at->format('d/m/Y') : '';
    }
}
