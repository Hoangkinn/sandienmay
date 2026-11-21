<?php

namespace App\Http\Middleware;

use App\Helpers\GetUrlThumb;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeFormatOutput
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        $content = $response->getContent();

        $this->addLazy($content);
        $this->replaceIframeWithDiv($content);
//        $this->replaceDomainMayQuetRac($content);
//        $this->addVerAsset($content);
        $this->cleanTelHref($content);
        $content = html_entity_decode($content);

        $response->setContent($content);
        return $response;
    }
    private function addLazy(&$content): void
    {
        $content = preg_replace_callback('#<img([^>]*?)/?>#', function ($m){
            if (!str_contains($m[1], ' loading=')) $m[1] .= ' loading=lazy';
            if (!str_contains($m[1], ' decoding=')) $m[1] .= ' decoding=async';
            if (!str_contains($m[1], ' fetchpriority=')) $m[1] .= ' fetchpriority=low';
            return "<img$m[1]>";
        }, $content);
    }
    private function replaceIframeWithDiv(&$content)
    {
        // Regex để tìm thẻ iframe
        $iframePattern = '#<iframe.*?(https://(?:www.)?youtube\.com/embed[^"\']+).*?></iframe>#';

        // Thực hiện preg_replace với callback
        $content = preg_replace_callback($iframePattern, function ($matches) {
            $matches[0] = str_replace('<iframe', '<iframe loading=lazy', $matches[0]);

            $thumbYtb = GetUrlThumb::youtube($matches[1]);
            $imgThumb = "<img loading='lazy' width='1280' height='720' src='/img-new/icons/16x9.webp' data-lazy=\"$thumbYtb\" alt=\"Xem video\">";

            return "<div class=\"box-iframe d-flex flex-center\">$matches[0] <div class='changeIframe mw-100 cursor-pointer position-relative d-flex flex-center'>$imgThumb <i class='pe-none bg-none icon-youtube fs-50 position-absolute text-red'></i></div></div>";
        }, $content);
    }
    private function replaceDomainMayQuetRac(&$content): void
    {
        if (request()->getHost() != 'trungtammuasam.vn') return;
        $content = str_replace('mayquetrac.com', 'trungtammuasam.vn', $content);
    }
    private function addVerAsset(&$content): void
    {
        $ver = config('app.ver_asset');
        if (!$ver) return;
        $content = str_replace(['.css"', '.js"'], [".css?ver=$ver\"", ".js?ver=$ver\""], $content);
    }
    private function cleanTelHref(&$content): void
    {
        preg_match_all('#href=["\']tel:[0-9]*\s+[0-9\s]*#i', $content, $matches);
        foreach (array_unique($matches[0]) as $i){
            $content = str_replace($i, str_replace(' ', '', $i), $content);
        }
    }
}
