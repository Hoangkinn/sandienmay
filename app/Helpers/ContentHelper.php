<?php

namespace App\Helpers;

class ContentHelper
{
    public static function replaceIframeWithDiv($content)
    {
        // Regex để tìm thẻ iframe
        $iframePattern = '#<iframe.*?(https://(?:www.)?youtube\.com/embed[^"\']+).*?></iframe>#';

        // Thực hiện preg_replace với callback
        $newContent = preg_replace_callback($iframePattern, function ($matches) {
            $youtubeId = self::getYoutubeIdFromEmbedUrl($matches[1]);
            if (! $youtubeId){
                return $matches[0];
            }
            $imgThumb = "<img loading='lazy' width='640' height='480' src=\"https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg\" alt=\"Xem video\" rel=\"nofollow\">";

            return "<div class=\"box-iframe d-flex flex-center\">$matches[0] <div class='changeIframe cursor-pointer position-relative d-flex flex-center'>$imgThumb <i class='pe-none bg-none icon-youtube fs-50 position-absolute'></i></div></div>";
        }, $content);

        return $newContent;
    }

    private static function getYoutubeIdFromEmbedUrl(string $url): string
    {
        preg_match('#youtube\\.com/(?:embed/|v/)([^"&?/ ]+)#', $url, $matches);
        return $matches[1] ?? '';
    }
}
