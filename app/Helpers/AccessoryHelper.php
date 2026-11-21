<?php

namespace App\Helpers;

use App\Enums\Status;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class AccessoryHelper
{
    /**
     * Từ các danh mục đang chọn, dựng bản đồ nhánh: anchor (danh mục cha, hoặc chính danh mục đó nếu đã là cha)
     * → danh mục con tên "Phụ kiện ..." + phạm vi danh mục con của anchor.
     *
     * @return array<int, array{anchor_id: int, accessory_category_id: ?int, scope_category_ids: int[]}>
     */
    public static function branchMap(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if (empty($categoryIds)) {
            return [];
        }

        $selected = Category::whereIn('id', $categoryIds)->get(['id', 'parent_id']);
        $anchorIds = $selected->map(fn ($c) => $c->parent_id ?: $c->id)->unique()->values()->all();
        $children = Category::whereIn('parent_id', $anchorIds)->get(['id', 'parent_id', 'name']);

        $map = [];
        foreach ($anchorIds as $anchorId) {
            $childIds = $children->where('parent_id', $anchorId)->pluck('id')->all();
            $accessoryCat = $children->where('parent_id', $anchorId)
                ->first(fn ($c) => str_contains(self::normalizeName($c->name), 'phu kien'));
            $map[$anchorId] = [
                'anchor_id' => $anchorId,
                'accessory_category_id' => $accessoryCat?->id,
                'scope_category_ids' => array_merge([$anchorId], $childIds),
            ];
        }

        return $map;
    }

    /**
     * Id các danh mục con "Phụ kiện ..." ứng với danh sách danh mục đang chọn.
     */
    public static function accessoryCategoryIds(array $categoryIds): array
    {
        return array_values(array_filter(array_map(
            fn ($b) => $b['accessory_category_id'],
            self::branchMap($categoryIds)
        )));
    }

    /**
     * Phụ kiện của nhánh: các sản phẩm đang nằm trong danh mục con "Phụ kiện ..."
     * (sản phẩm actived, loại trừ chính sản phẩm đang sửa). checked luôn false —
     * Người dùng tick cái nào thì lúc lưu mới gán cái đó vào danh mục phụ kiện.
     * Sync chỉ gán (attach), không gỡ — tránh lưu sản phẩm khác trong cùng nhánh
     * làm trống danh mục phụ kiện.
     */
    public static function candidates(array $categoryIds, ?int $excludeProductId = null): array
    {
        $accessoryCatIds = self::accessoryCategoryIds($categoryIds);
        if (empty($accessoryCatIds)) {
            return [];
        }

        $memberIds = DB::table('m_category_product')
            ->whereIn('category_id', $accessoryCatIds)
            ->pluck('product_id')
            ->unique();

        if ($memberIds->isEmpty()) {
            return [];
        }

        return DB::table('m_products')
            ->whereIn('id', $memberIds)
            ->where('status', Status::ACTIVED->value)
            ->when($excludeProductId, fn ($q) => $q->where('id', '!=', $excludeProductId))
            ->orderBy('name')
            ->get(['id', 'name', 'regular_price', 'sale_price'])
            ->map(function ($r) {
                $price = $r->sale_price > 0 ? $r->sale_price : $r->regular_price;
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'price' => $price,
                    'checked' => false,
                ];
            })->values()->all();
    }

    /**
     * Đồng bộ tick phụ kiện: ticked → gán vào danh mục "Phụ kiện ..." của nhánh chứa nó.
     * Chỉ gán, không gỡ: picker không pre-tick nên lưu sản phẩm khác trong cùng nhánh
     * mà không động vào picker sẽ không làm trống danh mục phụ kiện.
     */
    public static function syncAssignments(array $categoryIds, array $tickedIds, int $excludeProductId): void
    {
        $tickedIds = array_values(array_unique(array_filter(array_map('intval', $tickedIds))));
        if (empty($tickedIds)) {
            return;
        }
        $tickedSet = array_fill_keys($tickedIds, true);
        $pivot = DB::table('m_category_product');

        foreach (self::branchMap($categoryIds) as $branch) {
            $accCatId = $branch['accessory_category_id'];
            if (!$accCatId) {
                continue;
            }

            $scopeProductIds = DB::table('m_category_product')
                ->whereIn('category_id', $branch['scope_category_ids'])
                ->pluck('product_id')
                ->unique()
                ->filter(fn ($id) => $id !== $excludeProductId)
                ->values()
                ->all();
            if (empty($scopeProductIds)) {
                continue;
            }

            $currentMembers = $pivot->clone()->where('category_id', $accCatId)
                ->whereIn('product_id', $scopeProductIds)
                ->pluck('product_id')
                ->all();
            $memberSet = array_fill_keys($currentMembers, true);

            $toAttach = [];
            foreach ($scopeProductIds as $pid) {
                if (isset($tickedSet[$pid]) && !isset($memberSet[$pid])) {
                    $toAttach[] = ['category_id' => $accCatId, 'product_id' => $pid];
                }
            }
            if ($toAttach) {
                $pivot->clone()->insertOrIgnore($toAttach);
            }
        }
    }

    private static function normalizeName(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        return strtr($name, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ]);
    }
}
