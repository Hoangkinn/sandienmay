<?php
namespace App\Services\FeServices;

use App\Models\AttributeOption;
use App\Models\AttributeValue;
use App\Models\Product;

class AttributeOptionService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(AttributeOption::class));
        $this->selectList = ['*'];
    }
    public function checkAttributeValueBeforeAddCart(int $productId, array $attributeIds): bool
    {
        $products = resolve(ProductService::class)->getByIds([$productId]);
        if ($products->isEmpty()) {
            abort(404);
        }
        /** @var Product $product */
        $product = $products->first();
        $items = $product->attributeOptions()->with('attribute')->select('id', 'attribute_id')->where('status', 1)->whereIn('id', $attributeIds)->get();
        $attributes = $items->pluck('attribute')->unique('id')->toArray();

        return (count($items) == count($attributes)) && (count($items) == count($attributeIds));
    }
}
