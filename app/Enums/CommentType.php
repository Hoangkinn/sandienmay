<?php

namespace App\Enums;

enum CommentType: String
{
    case POST = 'post';
    case PRODUCT = 'product';
    case PAGE = 'page';

    public function label(): string
    {
        return match ($this) {
            static::POST => 'Tin tức',
            static::PAGE => 'Bài viết',
            static::PRODUCT => 'Sản phẩm',
        };
    }
}
