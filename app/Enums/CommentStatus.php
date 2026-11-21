<?php

namespace App\Enums;

enum CommentStatus: String
{
    case YES = 'yes';
    case NO = 'no';

    public function label(): string
    {
        return match ($this) {
            static::YES => 'Đã duyệt',
            static::NO => 'Không duyệt',
        };
    }
}
