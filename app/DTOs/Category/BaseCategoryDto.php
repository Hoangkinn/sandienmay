<?php

namespace App\DTOs\Category;

use App\DTOs\BaseDto;
use App\Models\Category;

abstract class BaseCategoryDto extends BaseDto
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $url;
    public readonly string $image;
    public readonly string $icon;
    public readonly string $banner;
    public readonly string $type;
    public readonly int $order;
    public readonly int $parent_id;
    public readonly bool $is_home_page;
    public readonly bool $is_menu;
    public readonly bool $is_featured;
    public readonly ?string $badge;
    public readonly ?string $display_type;
    public readonly ?string $schema;
    public function __construct(Category $category)
    {
        $this->id = $category->id;
        $this->name = $category->name;
        $this->url = route('frontend.slug', $category->slug);
        $this->image = $category->image ? "/storage/{$category->image}" : '';
        $this->icon = $category->icon ? "/storage/{$category->icon}" : '';
        $this->banner = self::bannerPath($category->icon);
        $this->type = $category->type;
        $this->order = $category->order ?? 0;
        $this->parent_id = (int) $category->parent_id;
        $this->is_home_page = (bool) $category->is_home_page;
        $this->is_menu = (bool) $category->is_menu;
        $this->is_featured = (bool) $category->is_featured;
        $this->badge = $category->badge ?: null;
        $this->display_type = $category->display_type ?? null;
        $this->schema = $category->schema ?? null;
    }

    /**
     * Cột icon lưu JSON {"main": "ảnh thumb", "extra": "ảnh banner danh mục"}
     */
    private static function bannerPath(?string $icon): string
    {
        $data = json_decode((string) $icon, true);
        $extra = is_array($data) ? ($data['extra'] ?? '') : '';

        return $extra ? "/storage/{$extra}" : '';
    }

    protected function addToc(?string &$content): array
    {
        if (! $content) return [];

        $index = 0;
        $toc = [];

        $content = preg_replace_callback('#<h([23])[^>]*>(.*?)</h\1>#', function ($m) use (&$index, &$toc) {
            $level = (int) $m[1];
            $title = strip_tags($m[2]);
            $id = 'toc' . (++$index);

            if ($level === 2) {
                $toc[] = [
                    'id' => $id,
                    'title' => $title,
                    'children' => []
                ];
            } else {
                if (!empty($toc)) {
                    $tocIndex = count($toc) - 1;
                    $toc[$tocIndex]['children'][] = [
                        'id' => $id,
                        'title' => $title
                    ];
                }
            }

            return preg_replace(
                '#<h([23])([^>]*)>#',
                "<h$1 id=\"$id\"$2>",
                $m[0],
                1
            );
        }, $content);

        return $toc;
    }
}
