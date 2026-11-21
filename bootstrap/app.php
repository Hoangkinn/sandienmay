<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Loại bỏ hoàn toàn middleware kiểm tra mã hóa URL để xử lý triệt để lỗi 500
        $middleware->remove(\Illuminate\Foundation\Http\Middleware\ValidatePathEncoding::class);

        // Đăng ký alias cho middleware phân quyền
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
        ]);

        // Cấu hình append các middleware web và agent discovery header vào chung một chỗ
        $middleware->web(append: [
            \App\Http\Middleware\CheckPublicUrl::class,
        ]);

        // Thêm header cho AI Agent
        $middleware->append(\App\Http\Middleware\AgentDiscoveryHeaders::class);

        // Bản thử nghiệm: gắn X-Robots-Tag noindex, nofollow cho toàn bộ response
        $middleware->append(\App\Http\Middleware\NoIndex::class);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();