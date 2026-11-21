<?php

namespace App\Enums;

enum Target: String
{
    case BLANK = '_blank';
    case SELF = '_self';

    public function label(): string
    {
        return match ($this) {
            static::BLANK => 'Mở tab mới',
            static::SELF => 'Mở trực tiếp',
        };
    }
}
