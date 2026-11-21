<?php

namespace App\DTOs\Product;

use App\Models\New_Product;

class ProductListDto extends BaseProductDto
{
    public readonly int $regular_price;
    public readonly int $sale_price;
    public readonly bool $btnBuyNow;
    public readonly string $image;
    public readonly array $images;
    public readonly string $promotion;
    public readonly bool $is_hot;
    public readonly bool $is_new;
    public readonly int $category_id;
    public readonly int $sold_quantity;
    public readonly int $discount_percent;

    public function __construct(New_Product $product)
    {
        parent::__construct($product);
        $this->regular_price = $product->regular_price;
        $this->sale_price = $product->sale_price;
        $this->btnBuyNow = (bool) $product->regular_price;
        $this->image = ($product->relationLoaded('images') && !empty($product->images[0])) ? $product->images[0]['url'] : '';
        
        $imagesArray = [];
        if ($product->relationLoaded('images') && $product->images) {
            foreach ($product->images as $img) {
                // Kiểm tra xem cấu trúc ảnh là mảng hay object
                $url = is_array($img) ? ($img['url'] ?? '') : ($img->url ?? '');
                if (!empty($url)) {
                    $imagesArray[] = $url;
                }
            }
        }
        
        // Nếu quan hệ chưa load hoặc mảng rỗng, fallback về cột ảnh đơn mặc định
        if (empty($imagesArray) && !empty($product->image)) {
            $imagesArray[] = $product->image;
        }
        $this->images = $imagesArray;

        $this->is_hot = (bool) $product->is_hot;
        $this->is_new = (bool) $product->is_new;
        $this->category_id = $product->category_id ?: 0;
        $this->sold_quantity = $product->sold_quantity;
        if ($product->regular_price > 0 && $product->sale_price > 0 && $product->sale_price < $product->regular_price) {
            $this->discount_percent = round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100);
        } else {
            $this->discount_percent = 0;
        }
        $this->promotion = $product->promotion ?? '';
    }
}
