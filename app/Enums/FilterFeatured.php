<?php

namespace App\Enums;

enum FilterFeatured: String
{
    case IS_NEW = 'is_new';
    case IS_HOT = 'is_hot';
    case IS_FLASH_SALE = 'is_flash_sale';

    public function label(): string
    {
        return match ($this) {
            static::IS_NEW => 'Mới',
            static::IS_HOT => 'Best seller',
            static::IS_FLASH_SALE => 'Flash sale',
        };
    }
}
