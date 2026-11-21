<?php

namespace App\Http\Middleware;

use App\Helpers\ContentHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeOptimizeSpeed
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);
        $content = $response->getContent();

//        mặc định thêm loading, decoding, fetchpriority
        $content = preg_replace_callback('#<img ([^>]+)>#', function ($m){
            if (! strpos($m[1], ' loading=')) $m[1] .= ' loading=lazy';
            if (! strpos($m[1], ' decoding=')) $m[1] .= ' decoding=async';
            if (! strpos($m[1], ' fetchpriority=')) $m[1] .= ' fetchpriority=low';
            return "<img $m[1]>";
        }, $content);

        // Tối ưu <iframe>
        $content = preg_replace_callback('#<iframe ([^>]+)>#', function ($m){
            if (!strpos($m[1], ' loading=')) $m[1] .= ' loading=lazy';
            if (!strpos($m[1], ' referrerpolicy=')) $m[1] .= ' referrerpolicy=no-referrer';
            if (!strpos($m[1], ' fetchpriority=')) $m[1] .= ' fetchpriority=low';
            return "<iframe {$m[1]}>";
        }, $content);
        $content = ContentHelper::replaceIframeWithDiv($content);

        $response->setContent($content);
        return $response;
    }
}
