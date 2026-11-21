<?php

namespace App\Enums;

enum Status: String
{
    case ACTIVED = 'actived';
    case INACTIVED = 'inactived';

    public function label(): string
    {
        return match ($this) {
            static::ACTIVED => 'Hiển thị',
            static::INACTIVED => 'Ẩn',
        };
    }
}
