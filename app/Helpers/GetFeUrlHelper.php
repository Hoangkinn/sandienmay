<?php

namespace App\Helpers;

class GetFeUrlHelper
{
    public static function home(): string
    {
        return route('frontend.page.home');
    }
    public static function search(): string
    {
        return route('frontend.page.search');
    }
}
