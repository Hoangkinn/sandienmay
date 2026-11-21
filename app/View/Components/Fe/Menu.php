<?php

namespace App\View\Components\Fe;

use App\Services\FeServices\CategoryService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Menu extends Component
{
    /**
     * Thứ tự + nhãn các nhóm danh mục con trong drawer mobile (khoá theo cột display_type).
     */
    public array $groupLabels = [
        'brand' => 'Thương hiệu',
        'attribute' => 'Nhu cầu',
        'spec' => 'Thông số',
        'price' => 'Phân khúc giá',
    ];

    public array $categories;

    /**
     * @param string $mode mega (panel header) | sidebar (danh mục dọc trang chủ) | drawer (mobile)
     */
    public function __construct(public string $mode = 'drawer')
    {
        // Xóa cache cũ nếu cần thiết để đảm bảo nhận dữ liệu mới nhất từ DTO
        \Illuminate\Support\Facades\Cache::forget('product_categories_all');

        $categories = resolve(CategoryService::class)->getProductCategories();

        $collection = collect($categories);

        // Lọc ra danh mục cấp 1 có is_menu = true
        $cateParents = $collection->filter(function ($item) {
            return empty($item['parent_id']) && ($item['is_menu'] ?? false);
        });

        // Danh mục con gắn thương hiệu qua category_brand_pivot thường không có ảnh riêng
        // (admin chỉ chọn thương hiệu) -> lấy logo của thương hiệu làm ảnh đại diện.
        $brandLogos = \Illuminate\Support\Facades\DB::table('category_brand_pivot')
            ->join('brands', 'brands.id', '=', 'category_brand_pivot.brand_id')
            ->whereNotNull('brands.image')
            ->pluck('brands.image', 'category_brand_pivot.category_id');

        // Gom nhóm danh mục con theo display_type ('brand' và 'attribute') cho drawer mobile,
        // đồng thời dựng thêm cột danh mục cấp 2 (subcols) cho panel mega / sidebar trang chủ.
        $this->categories = $cateParents->map(function ($parent) use ($collection, $brandLogos) {
            $children = $collection->filter(function ($item) use ($parent) {
                return $item['parent_id'] == $parent['id'] && ($item['is_menu'] ?? false);
            })->reject(function ($item) {
                // Danh mục giả lập kiểu lọc giá/thông số không còn dùng trong menu
                return in_array($item['display_type'], ['price', 'spec'], true);
            })->map(function ($child) use ($collection, $brandLogos) {
                if (empty($child['image']) && !empty($brandLogos[$child['id']])) {
                    $child['image'] = '/storage/' . $brandLogos[$child['id']];
                }

                $child['children'] = $collection->filter(function ($item) use ($child) {
                    return $item['parent_id'] == $child['id'] && ($item['is_menu'] ?? false);
                })->reject(function ($item) {
                    return in_array($item['display_type'], ['price', 'spec'], true);
                })->values()->all();

                return $child;
            })->values();

            $grouped = $children->groupBy('display_type');
            $ordered = [];
            foreach (array_keys($this->groupLabels) as $key) {
                // 'price' và 'spec' không còn sinh danh mục con trong menu
                if (in_array($key, ['price', 'spec'], true)) {
                    continue;
                }
                if ($grouped->has($key)) {
                    $ordered[$key] = $grouped->get($key)->toArray();
                    $grouped->forget($key);
                }
            }
            // Nhóm lạ (nếu admin thêm display_type mới) vẫn giữ nguyên thứ tự gặp trước
            foreach ($grouped as $key => $items) {
                $ordered[$key] = $items->toArray();
            }

            $parent['childrens'] = $ordered;
            $parent['subcols'] = $children->all();

            return $parent;
        })->values()->toArray();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.fe.menu');
    }
}
