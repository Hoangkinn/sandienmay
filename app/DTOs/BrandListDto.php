<?php

namespace App\DTOs;

use App\Models\Brand;

class BrandListDto extends BaseDto
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $image;

    public function __construct(Brand $item)
    {
        $this->id = $item->id;
        $this->name = $item->name;
        $this->image = $item->image ?: '';
    }
}
