<?php
namespace App\Services\FeServices;

use App\DTO\AttributeValueDto;
use App\DTO\Product\ProductListDto;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;

class CartService
{
    protected string $sessionKey = 'cart';

    public function all(): array
    {
        return session($this->sessionKey, []);
    }

    public function add(int $id, int $quantity = 1, ?array $attributeValueIds = []): void
    {
        $key = $this->generateKey($id, $attributeValueIds);
        $cart = $this->all();
        $cart[$key] = ($cart[$key] ?? 0) + $quantity;
dd($cart);
        session([$this->sessionKey => $cart]);
    }

    public function update(string $key, int $quantity): void
    {
        $cart = $this->all();

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key] = $quantity;
        }

        session([$this->sessionKey => $cart]);
    }

    public function remove(string $key): void
    {
        $cart = $this->all();
        unset($cart[$key]);
        session([$this->sessionKey => $cart]);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    public function totalQuantity(): int
    {
        return array_sum($this->all());
    }

    public function getProducts(): array
    {
        $cart = $this->all();
        if (empty($cart)) return [];

        /** @var ProductService $productService */
        $productService = app(ProductService::class);
        $attributeValueService = resolve(AttributeValueService::class);
        [$productIds, $attributeValueIds] = $this->detachProductIdsAndAttributeValueIds();
        $products = $productService->getByIds($productIds);
        if ($attributeValueIds){
            $products->load([
                'attributeValues' => fn (BelongsToMany $builder) => $attributeValueService->getList($builder)->whereIn('id', $attributeValueIds)
            ]);
            $attributeValues = AttributeValueDto::from($products->pluck('attributeValues')->flatten());
        } else {
            $attributeValues = [];
        }

        $products = ProductListDto::from($products);

        $result = [];

        foreach ($cart as $key => $quantity) {
            $parts = explode('-', $key);
            $productId = $parts[0];
            $attributeIds = array_slice($parts, 1);

            // Tìm product tương ứng
            $product = Arr::first($products, fn($i) => $i['id'] == $productId);
            if (! $product) continue;
            $attributeValueItems = array_filter($attributeValues, fn ($item) => in_array($item['id'], $attributeIds));
            $product['price'] += array_sum(array_column($attributeValueItems, 'extraPrice'));
            $product['quantity'] = $quantity;
            $product['attributeValues'] = array_column($attributeValueItems, 'value');
            $product['idCart'] = $key;

            $result[] = $product;
        }

        return $result;
    }
    private function generateKey(int $productId, ?array $attributeValueIds = []): string
    {
        if ($attributeValueIds){
            sort($attributeValueIds);
            return $productId . '-' . implode('-', $attributeValueIds);
        } else {
            return $productId;
        }
    }
    private function detachProductIdsAndAttributeValueIds(): array
    {
        $cart = $this->all();
        $keys = array_keys($cart);
        $productIds = $attributeValueIds = [];
        foreach ($keys as $key) {
            $arrayIds = explode('-', $key);
            $productIds[] = $arrayIds[0];
            $attributeValueIds = array_merge($attributeValueIds, array_slice($arrayIds, 1));
        }
        return [array_unique($productIds), array_unique($attributeValueIds)];
    }
    public function checkExists(string $key): bool
    {
        return $this->all()[$key] ?? false;
    }
}
