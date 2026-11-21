<?php

namespace App\Enums;

enum Vat: int
{
    case VAT_NO = 0;
    case VAT_YES = 1;

    public function label(): string
    {
        return match ($this) {
            static::VAT_NO => 'Giá chưa bao gồm VAT',
            static::VAT_YES => 'Giá bao gồm VAT',
        };
    }
}
