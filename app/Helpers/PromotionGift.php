<?php

namespace App\Helpers;

/**
 * Cột m_products.promotion lưu HTML "<ul><li><img ...>Tên quà</li>...</ul>".
 * Lớp này dịch qua lại giữa HTML đó và danh sách dòng {name, image} để màn admin
 * nhập theo từng dòng mà không phải đổi schema.
 */
class PromotionGift
{
    public static function toHtml(array $rows): string
    {
        $items = '';

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $image = trim((string) ($row['image'] ?? ''));

            if ($name === '' && $image === '') {
                continue;
            }

            $items .= '<li>';
            if ($image !== '') {
                $items .= '<img src="' . e($image) . '" alt="' . e($name) . '">';
            }
            $items .= e($name) . '</li>';
        }

        return $items === '' ? '' : '<ul>' . $items . '</ul>';
    }

    /**
     * Nội dung có đúng dạng danh sách quà tặng không? HTML tự do (đoạn văn, bảng,
     * ảnh to...) mà ô soạn thảo cũ từng cho phép thì KHÔNG — nhờ vậy admin không
     * vô tình làm mất định dạng khi lưu lại.
     */
    public static function isGiftList($html): bool
    {
        $html = trim((string) $html);

        if ($html === '') {
            return true;
        }

        $withoutGiftTags = preg_replace('#</?(?:ul|li|img)\b[^>]*>#i', '', $html);

        return stripos((string) $withoutGiftTags, '<') === false;
    }

    public static function parse($html): array
    {
        $html = trim((string) $html);

        if ($html === '' || !self::isGiftList($html)) {
            return [];
        }

        $rows = [];

        if (preg_match_all('#<li[^>]*>(.*?)</li>#is', $html, $items)) {
            foreach ($items[1] as $inner) {
                $image = '';

                if (preg_match('#<img[^>]+src="([^"]+)"#i', $inner, $src)) {
                    $image = $src[1];
                    $inner = preg_replace('#<img[^>]*>#i', '', $inner);
                }

                $name = trim(html_entity_decode(strip_tags((string) $inner), ENT_QUOTES, 'UTF-8'));

                if ($name !== '' || $image !== '') {
                    $rows[] = ['name' => $name, 'image' => $image];
                }
            }
        }

        return $rows;
    }
}
