<?php

namespace App\Helpers;

class GetUrlThumb
{
    public static function youtube(string $input, int $quality = 4): string
    {
        // Regex lấy ID từ các dạng link YouTube phổ biến
        if (preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $input,
            $matches
        )) {
            $id = $matches[1];
        } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            $id = $input;
        } else {
            return ''; // Không hợp lệ
        }
        return match ($quality) {
            0 => "https://i.ytimg.com/vi/$id/default.jpg",
            1 => "https://i.ytimg.com/vi/$id/mqdefault.jpg",
            2 => "https://i.ytimg.com/vi/$id/hqdefault.jpg",
            3 => "https://i.ytimg.com/vi/$id/sddefault.jpg",
            4 => "https://i.ytimg.com/vi/$id/maxresdefault.jpg",
        };
    }
}
