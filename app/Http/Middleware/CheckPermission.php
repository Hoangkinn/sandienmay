<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('backend.login');
        }
        $type = $request->route('type');
        if ($type === 'post' && str_starts_with($permission, 'category_product_')) {
            $permission = str_replace('category_product_', 'post_category_', $permission);
        }

        // Lấy dữ liệu thô từ cột phân quyền (thử lấy cột role_ids hoặc role tùy thuộc vào model)
        $roleField = $user->role_ids ?? $user->role ?? '';

        // Trường hợp 1: Nếu lưu dạng chữ thuần (Ví dụ: "admin")
        if (is_string($roleField)) {
            $cleaned = strtolower(trim($roleField));
            if ($cleaned === 'admin') {
                return $next($request);
            }
            
            // Nếu chuỗi bắt đầu bằng dấu '{' (JSON string chưa được cast)
            if (str_starts_with($roleField, '{')) {
                $decoded = json_decode($roleField, true);
                if (is_array($decoded)) {
                    if (isset($decoded['role']) && strtolower(trim($decoded['role'])) === 'admin') {
                        return $next($request);
                    }
                    if (isset($decoded['permissions']) && is_array($decoded['permissions'])) {
                        if (in_array($permission, $decoded['permissions'])) {
                            return $next($request);
                        }
                    }
                }
            }
        }

        // Trường hợp 2: Nếu dữ liệu đã tự động cast thành mảng (array) bởi Laravel Model
        if (is_array($roleField)) {
            // Kiểm tra nếu mảng có chứa quyền hoặc role admin
            if (isset($roleField['role']) && strtolower(trim($roleField['role'])) === 'admin') {
                return $next($request);
            }
            if (isset($roleField['permissions']) && is_array($roleField['permissions'])) {
                if (in_array($permission, $roleField['permissions'])) {
                    return $next($request);
                }
            }
            if (in_array($permission, $roleField)) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền truy cập chức năng này.');
    }
}