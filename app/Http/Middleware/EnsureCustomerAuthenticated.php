<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('customers')->check()) {
            // Không dùng `url.intended` của Laravel: key đó login admin (/backend/login) cũng ghi/đọc,
            // khách bấm "đăng nhập" có thể bị đá vào trang định hướng của admin.
            $request->session()->put('customer.intended', $request->fullUrl());

            return redirect()->route('frontend.customer.login');
        }

        return $next($request);
    }
}
