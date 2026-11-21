<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CleanPublicPrefixFromLinks
{
    protected $staticExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp',
        'css', 'js', 'woff', 'woff2', 'ttf', 'eot', 'map',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type', '');

        if (
            $response instanceof \Illuminate\Http\Response &&
            (
                str_contains($contentType, 'text/html') ||
                str_contains($contentType, 'application/xml') ||
                str_contains($contentType, 'text/xml')
            )
        ) {
            $content = $response->getContent();

            $host = $request->getSchemeAndHttpHost(); // https://domain.com

            // Regex cho href, action, hoặc URL trong XML (bắt /public/ hoặc /index.php/)
            $pattern = '#\b(href|action)?=?["\']?(\/|'.preg_quote($host, '#').'\/)(public/|index\.php/)([^"\'<>\s]+)#i';

            $content = preg_replace_callback(
                $pattern,
                function ($matches) use ($host) {
                    $attr = $matches[1] ?? '';        // href | action | rỗng
                    $prefix = $matches[2];            // / hoặc https://domain.com/
                    $path = $matches[4];              // phần sau public/ hoặc index.php/
                    $ext = pathinfo($path, PATHINFO_EXTENSION);

                    // Nếu là file tĩnh (css, js, ảnh, v.v.) thì giữ nguyên
                    if (in_array(strtolower($ext), $this->staticExtensions)) {
                        return $matches[0];
                    }

                    // Loại bỏ public/ hoặc index.php/
                    return ($attr ? $attr . '=' : '') . '"' . $prefix . $path . '"';
                },
                $content
            );

            $response->setContent($content);
        }

        return $response;
    }
}
