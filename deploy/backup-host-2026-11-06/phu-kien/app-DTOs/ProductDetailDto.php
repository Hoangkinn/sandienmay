<?php

namespace App\DTOs\Product;

use App\Enums\ProductStatus;
use App\Models\New_Product;

class ProductDetailDto extends BaseProductDto
{
    public readonly int $price;
    public readonly int $sale_price;
    public readonly string $textVat;
    public readonly string $embedYoutube;
    public readonly string $thumbYoutube;
    public readonly string $content;
    public array $toc = [];
    public readonly string $seoTitle;
    public readonly string $seoDescription;
    public readonly string $seoKeywords;
    public readonly string $seoUrlCanonical;
    public readonly ?string $schema;
    public readonly array $images;
    public readonly bool $instock;
    public readonly int $warranty;
    public readonly int $discount_percent;
    public readonly string $promotion;
    public readonly string $youtube_id;

    public function __construct(New_Product $product)
    {
        parent::__construct($product);
        $this->price = $product->regular_price ?: 0;
        $this->sale_price = $product->sale_price ?: 0;
        $this->textVat = $product->is_vat ? 'Giá bao gồm VAT' : 'Giá chưa bao gồm VAT';
        $this->embedYoutube = $product->youtube_id
            ? "https://www.youtube-nocookie.com/embed/$product->youtube_id"
            : '';
        $this->thumbYoutube = $product->youtube_id ? "https://i.ytimg.com/vi/$product->youtube_id/hqdefault.jpg" : '';

        $content = (string) $product->content;
        $this->toc = $this->addToc($content);
        $this->content = $content;
        $this->seoTitle = (string) $product->seo_title;
        $this->seoDescription = (string) $product->seo_description;
        $this->seoKeywords = (string) $product->seo_keywords;
        $this->seoUrlCanonical = (string) $product->seo_url_canonical;
        $this->instock = $product->stock_status == ProductStatus::IN_STOCK->value ? true : false;
        $this->warranty = $product->warranty ?? 0 ;
        if ($product->regular_price > 0 && $product->sale_price > 0 && $product->sale_price < $product->regular_price) {
            $this->discount_percent = round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100);
        } else {
            $this->discount_percent = 0;
        }

        $images = json_decode($product->images, true);
        if ($images){
            $this->images = array_map(function ($i){
                return $i['url'];
            }, $images);
        } else {
            $this->images = [];
        }

        $this->promotion = $product->promotion ?? '';
        $this->youtube_id = $product->youtube_id ?? '';
        $this->schema = $product->schema ?? \DB::table('m_products')->where('id', $product->id)->value('schema') ?? '';
    }
}
