<?php

namespace App\Enums;

enum ProductStatus: String
{
    case IN_STOCK = 'instock';
    case OUT_OF_STOCK = 'outofstock';

    public function label(): string
    {
        return match ($this) {
            static::IN_STOCK => 'Còn hàng',
            static::OUT_OF_STOCK => 'Hết hàng',
        };
    }
}
