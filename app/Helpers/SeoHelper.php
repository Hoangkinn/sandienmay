<?php

namespace App\Helpers;

class SeoHelper
{
    protected static string $title = '';
    protected static string $description = '';
    protected static string $keywords = '';
    protected static string $image = '';
    protected static string $url = '';
    protected static string $robots = 'index, follow';

    // public static function set(array $item): void
    // {
    //     static::setTitle($item['seoTitle']);
    //     static::setDescription($item['seoDescription']);
    //     static::setKeywords($item['seoKeyword']);
    //     static::setRobots($item['isIndex']);
    //     static::setUrl($item['canonical']);
    // }
    public static function set($item): void
    {
        // Kiểm tra nếu $item là Object (DTO) thì chuyển sang lấy thuộc tính, nếu là mảng thì lấy theo key mảng
        $seoTitle = is_object($item) ? ($item->seoTitle ?? '') : ($item['seoTitle'] ?? '');
        $seoDescription = is_object($item) ? ($item->seoDescription ?? '') : ($item['seoDescription'] ?? '');
        $seoKeyword = is_object($item) ? ($item->seoKeyword ?? '') : ($item['seoKeyword'] ?? '');
        $isIndex = is_object($item) ? ($item->isIndex ?? true) : ($item['isIndex'] ?? true);
        $canonical = is_object($item) ? ($item->canonical ?? '') : ($item['canonical'] ?? '');

        static::setTitle($seoTitle);
        static::setDescription($seoDescription);
        static::setKeywords($seoKeyword);

        // Chuyển đổi boolean isIndex thành chuỗi Robots chính xác
        $robotsRule = $isIndex ? 'index, follow' : 'noindex, nofollow';
        static::setRobots($robotsRule);

        static::setUrl($canonical);
    }

    public static function setTitle(string $title): static
    {
        static::$title = $title;
        return new static;
    }

    public static function setDescription(string $description): static
    {
        static::$description = $description;
        return new static;
    }

    public static function setKeywords(string $keywords): static
    {
        static::$keywords = $keywords;
        return new static;
    }

    public static function setImage(string $image): static
    {
        static::$image = $image;
        return new static;
    }

    public static function setUrl(string $url): static
    {
        static::$url = $url;
        return new static;
    }

    public static function setRobots(string $rule): static
    {
        static::$robots = $rule;
        return new static;
    }

    public static function render(): string
    {
        if (request()->route('slug') === 'san-pham-da-xem') {
            static::$robots = 'noindex, follow';
        }
        if (request()->route('slug') === 'san-pham-lien-quan') {
            static::$robots = 'noindex, follow';
        }
        if (request()->has('page') && request()->query('page') > 1) {
            static::$robots = 'index, follow';
        }
        if (config('app.no_index')) {
            static::$robots = 'noindex, nofollow';
        }
        return view('components/fe/seo-meta', [
            'title' => static::e(static::$title),
            'description' => static::e(static::$description),
            'keywords' => static::e(static::$keywords),
            'image' => static::e(static::$image),
            'url' => static::e(static::$url),
            'robots' => static::e(static::$robots),
        ])->render();
    }

    protected static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
