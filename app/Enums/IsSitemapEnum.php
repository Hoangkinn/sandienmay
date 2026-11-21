<?php

namespace App\Enums;

use App\Models\BaseModel;

enum IsSitemapEnum: int
{
    case Yes = 1;
    case No = 0;
    public function getTitle(): string
    {
        return match ($this){
            self::No => "K đưa vào sitemap",
            self::Yes => "Đưa vào sitemap",
        };
    }
    public static function getBool(BaseModel $model): bool
    {
        if ($model->exists)
            return $model->getRawOriginal('is_sitemap') == self::Yes->value;
        return true;
    }
}
