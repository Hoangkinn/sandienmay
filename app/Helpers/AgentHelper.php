<?php

namespace App\Helpers;

class AgentHelper
{
    public static function isMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/android|iphone|ipad|ipod|blackberry|windows phone|opera mini|mobile/i', $userAgent) === 1;
    }
}
