<?php

namespace App\Models;

use App\Enums\IsIndexEnum;
use App\Enums\IsSitemapEnum;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;

abstract class BaseModel extends Model
{
    use ActivityLoggable;
    protected $guarded = [];
    protected $casts = [
        'is_index' => IsIndexEnum::class,
        'is_sitemap' => IsSitemapEnum::class,
        'schema' => 'string',
    ];
    // public $timestamps = false;
}
