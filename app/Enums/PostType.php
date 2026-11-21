<?php

namespace App\Enums;

enum PostType: String
{
    case POST = 'post';
    case PAGE = 'page';

    public function label(): string
    {
        return match ($this) {
            static::POST => 'Bài viết',
            static::PAGE => 'Bài viết cố định',
        };
    }
}
