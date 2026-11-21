<?php

namespace App\Enums;

enum CategoryType: String
{
    case POST = 'post';
    case PRODUCT = 'product';

    public function label(): string
    {
        return match ($this) {
            static::POST => 'Danh mục bài viết',
            static::PRODUCT => 'Danh mục sản phẩm',
        };
    }
}
