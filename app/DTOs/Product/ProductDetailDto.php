<?php

namespace App\DTOs\Product;

use App\Enums\ProductStatus;
use App\Enums\Status;
use App\Helpers\AccessoryHelper;
use App\Models\New_Product;

class ProductDetailDto extends BaseProductDto
{
    public readonly array $accessories;
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
        $this->accessories = $this->loadAccessories($product);
    }

    /**
     * Phụ kiện "Thường được mua cùng": sản phẩm đang nằm trong danh mục con
     * "Phụ kiện ..." của nhánh danh mục cha mà sản phẩm thuộc về (tối đa 10).
     */
    private function loadAccessories(New_Product $product): array
    {
        $categoryIds = $product->categories->pluck('id')->all();
        $accessoryCatIds = AccessoryHelper::accessoryCategoryIds($categoryIds);
        if (empty($accessoryCatIds)) {
            return [];
        }

        $memberIds = \DB::table('m_category_product')
            ->whereIn('category_id', $accessoryCatIds)
            ->pluck('product_id')
            ->unique();

        if ($memberIds->isEmpty()) {
            return [];
        }

        $rows = New_Product::with('images')
            ->whereIn('id', $memberIds)
            ->where('status', Status::ACTIVED->value)
            ->where('id', '!=', $product->id)
            ->limit(10)
            ->get(['id', 'name', 'slug', 'image', 'regular_price', 'sale_price']);

        $accessories = [];
        foreach ($rows as $item) {
            $price = $item->sale_price > 0 ? $item->sale_price : $item->regular_price;
            $accessories[] = [
                'id' => $item->id,
                'name' => $item->name,
                'url' => route('frontend.slug', $item->slug),
                'image' => !empty($item->images[0]) ? $item->images[0]->url : $item->image,
                'price' => $price,
                'price_label' => $price > 0 ? number_format($price) . 'đ' : 'Liên hệ',
            ];
        }

        return $accessories;
    }
}
