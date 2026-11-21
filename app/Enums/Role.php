<?php

namespace App\Enums;

enum Role: String
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case Content = 'content';
    case SEO = 'seo';

    public function label(): string
    {
        return match ($this) {
            static::SUPER_ADMIN => 'Super Admin',
            static::ADMIN => 'Admin',
            static::Content => 'Content',
            static::SEO => 'Seo',
        };
    }
}
