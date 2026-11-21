<?php

namespace App\Enums;

enum ProductCategorySort: String
{
    case PRICE_ASC = 'price-asc';
    case PRICE_DESC = 'price-desc';
    case NEWEST = 'new';
    case OLDEST = 'old';
    // case DEFAULT = '';

    public function getTitle(): string
    {
        return match ($this) {
            self::PRICE_ASC => 'Giá tăng dần',
            self::PRICE_DESC => 'Giá giảm dần',
            self::NEWEST => 'Mới nhất',
            self::OLDEST => 'Cũ nhất',
            // self::DEFAULT => 'Mặc định',
        };
    }
}
