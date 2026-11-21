<?php

namespace App\DTOs\Product;

use App\DTOs\BaseDto;
use App\Models\New_Product;

abstract class BaseProductDto extends BaseDto
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $url;

    public function __construct(New_Product $product)
    {
        $this->id = $product->id;
        $this->name = $product->name;
        $this->url = route('frontend.slug', $product->slug);
    }
}
