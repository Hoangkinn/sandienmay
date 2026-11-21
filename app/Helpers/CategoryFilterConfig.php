<?php

namespace App\Helpers;

use App\Models\CategoryPriceBand;
use App\Models\CategorySpec;

/**
 * Lưu/xoá cấu hình "mức giá" và "thông số" của danh mục cấp 1 (m_category_price_bands,
 * m_category_specs). Hai bảng này thay cho kiểu tạo danh mục con display_type=price/spec,
 * vốn trỏ tới trang danh mục không có sản phẩm nào để lọc.
 *
 * Đồng bộ theo kiểu xoá-hết-rồi-ghi-lại: mỗi danh mục chỉ vài dòng, làm vậy đơn giản và
 * không để lại dòng cũ bị xoá khỏi form.
 */
class CategoryFilterConfig
{
    /**
     * @param array $rows mảng {label, min, max} theo đúng thứ tự hiển thị trên form
     */
    public static function syncPriceBands(int $categoryId, array $rows): void
    {
        CategoryPriceBand::where('category_id', $categoryId)->delete();

        $order = 0;
        foreach ($rows as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $min = self::toVnd($row['min'] ?? null);
            $max = self::toVnd($row['max'] ?? null);

            // Dòng rỗng (form luôn chừa sẵn 1 dòng trắng) hoặc không có ngưỡng nào thì bỏ.
            if ($label === '' || ($min === null && $max === null)) {
                continue;
            }

            // Đảo ngược nếu nhập nhầm "từ 10tr đến 3tr"; chỉ đổi khi có đủ hai đầu.
            if ($min !== null && $max !== null && $min > $max) {
                [$min, $max] = [$max, $min];
            }

            CategoryPriceBand::create([
                'category_id' => $categoryId,
                'label' => mb_substr($label, 0, 100),
                'min_price' => $min,
                'max_price' => $max,
                'order' => $order++,
            ]);
        }
    }

    /**
     * @param array $optionIds danh sách m_attribute_options được chọn (có thể trùng)
     */
    public static function syncSpecs(int $categoryId, array $optionIds): void
    {
        CategorySpec::where('category_id', $categoryId)->delete();

        $optionIds = array_values(array_unique(array_filter(array_map('intval', $optionIds))));
        if ($optionIds === []) {
            return;
        }

        // attribute_id lấy từ chính option, không tin giá trị gửi lên từ form.
        $attributes = \App\Models\AttributeOption::whereIn('id', $optionIds)->pluck('attribute_id', 'id');

        $order = 0;
        foreach ($optionIds as $optionId) {
            if (!$attributes->has($optionId)) {
                continue;
            }

            CategorySpec::create([
                'category_id' => $categoryId,
                'attribute_id' => $attributes[$optionId],
                'option_id' => $optionId,
                'order' => $order++,
            ]);
        }
    }

    /** "3.000.000" | "3,000,000" | "3000000" -> 3000000; rỗng/không có số -> null */
    public static function toVnd($value): ?int
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }
}
