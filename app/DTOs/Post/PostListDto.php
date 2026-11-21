<?php

namespace App\DTOs\Post;

use App\Models\Post;

class PostListDto extends BasePostDto
{
    public function __construct(Post $post)
    {
        parent::__construct($post);
    }
}
