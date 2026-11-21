<?php

namespace App\Enums;

enum UserStatus: String
{
    case ACTIVED = 'actived';
    case INACTIVED = 'inactived';

    public function label(): string
    {
        return match ($this) {
            static::ACTIVED => 'Hoạt động',
            static::INACTIVED => 'Tạm khóa',
        };
    }
}
