<?php

namespace App\Helpers;

use App\Services\FeServices\SettingFieldService;

class BreadcrumbHelper
{
    private static array $breadcrumbs = [];
    public static function reset(): void
    {
        static::$breadcrumbs = [];
    }
    public static function add(?string $title, ?string $url): void
    {
        static::$breadcrumbs[$url] = $title;
    }
    public static function render(): string
    {
        if (empty(static::$breadcrumbs)){
            return '';
        }
        $urlHome = GetFeUrlHelper::home();
        $titleHome = SettingFieldService::getKey('site_title');
        return view('components/fe/br', ['items' => static::$breadcrumbs, 'urlHome' => $urlHome, 'titleHome' => $titleHome]);
    }
}
