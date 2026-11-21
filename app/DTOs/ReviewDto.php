<?php

namespace App\DTOs;

use App\Models\Review;

class ReviewDto extends BaseDto
{
    public readonly string $avatar;
    public readonly string $name;
    public readonly string $content;
    public function __construct(Review $banner)
    {
        $this->avatar = $banner->avatar ? "/storage/$banner->avatar" : '';
        $this->name = $banner->name;
        $this->content = $banner->content ?? '';
    }
}
