<?php

namespace App\Enums;

use App\Models\BaseModel;

enum IsIndexEnum: int
{
    case No = 0;
    case Yes = 1;
    public function getTitle(): string
    {
        return match ($this){
            self::No => "Không index",
            self::Yes => "Có index",
        };
    }
    public static function getBool(BaseModel $model): bool
    {
        if ($model->exists)
            return $model->getRawOriginal('is_index') == self::Yes->value;
        return true;
    }
}
