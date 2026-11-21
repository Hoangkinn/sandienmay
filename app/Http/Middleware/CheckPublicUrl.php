<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPublicUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy URI gốc từ trình duyệt
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Nếu trong URL có chứa cụm "/public/" -> Chặn hết, không ngoại lệ
        if (str_contains($uri, '/public/')) {
            abort(404);
        }

        return $next($request);
    }
}