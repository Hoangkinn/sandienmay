<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $userRole = auth()->user()->role;
    $roleValue = is_object($userRole) && property_exists($userRole, 'value') ? $userRole->value : (string) $userRole;

    // Kiểm tra quyền
    if (!in_array(strtolower(trim($roleValue)), ['admin', 'super_admin'])) {
        abort(403, 'Bạn không có quyền truy cập trang lịch sử hoạt động.');
    }

        // 2. Lấy danh sách lịch sử kết hợp bộ lọc tìm kiếm
        $query = History::with('user')->latest();

        // Lọc theo tài khoản (user_id)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Lọc theo kiểu hoạt động (action: create, update, delete...)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Lọc theo kiểu dữ liệu / model (model_type: Post, Product...)
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Lọc theo nội dung mô tả (description)
        if ($request->filled('keyword')) {
            $query->where('description', 'like', '%' . $request->keyword . '%');
        }

        // Phân trang kết quả (mỗi trang 20 dòng)
        $histories = $query->paginate(40)->withQueryString();

        // Lấy danh sách users để hiển thị ra ô lọc tài khoản
        $users = User::all();

        return view('backend.history.index', compact('histories', 'users'));
    }
    public function show($id)
    {
        $history = History::with('user')->findOrFail($id);
        $oldData = json_decode($history->old_data, true) ?? [];
        $newData = json_decode($history->new_data, true) ?? [];

        return view('backend.history.show', compact('history', 'oldData', 'newData'));
    }

    public function restore($id)
    {
        $history = History::findOrFail($id);
        
        // Kiểm tra xem có dữ liệu old_data không
        if (empty($history->old_data)) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu cũ để khôi phục!');
        }

        $oldData = json_decode($history->old_data, true);

        if (empty($oldData) || !is_array($oldData)) {
            return redirect()->back()->with('error', 'Dữ liệu cũ không hợp lệ để khôi phục!');
        }

        // TRƯỜNG HỢP 1: Xử lý riêng cho SETTING (Cập nhật theo key-value)
        if ($history->model_type === 'Setting') {
            foreach ($oldData as $settingName => $settingValue) {
                \App\Models\Setting::where('setting_name', $settingName)
                    ->update(['setting_value' => $settingValue]);
            }

            // Ghi log hành động khôi phục setting
            \App\Services\ActivityLogService::log(
                'restore', 
                'Setting', 
                null, 
                "[Khôi phục dữ liệu] Đã khôi phục cấu hình hệ thống về trạng thái cũ",
                null, 
                $oldData
            );

            return redirect()->route('backend.history.show', $history->id)
                            ->with('success', 'Đã khôi phục cấu hình website thành công!');
        }

        // TRƯỜNG HỢP 2: Xử lý cho các Model thông thường (Product, Post,...)
        if (!$history->model_id || !$history->model_type) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin đối tượng để khôi phục!');
        }

        $modelClass = 'App\\Models\\' . $history->model_type;
        
        if (!class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Không tìm thấy Model tương ứng để khôi phục!');
        }

        $model = $modelClass::find($history->model_id);

        if (!$model) {
            return redirect()->back()->with('error', 'Đối tượng này đã bị xóa vĩnh viễn, không thể khôi phục!');
        }

        array_walk_recursive($oldData, function (&$value) {
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        });

        unset($oldData['id'], $oldData['created_at'], $oldData['updated_at']);
        $model->update($oldData);

        $modelName = class_basename($model);
        $title = $model->title ?? $model->name ?? $model->email ?? ('ID: ' . $model->id);
        
        \App\Services\ActivityLogService::log(
            'restore', 
            $modelName, 
            $model->id, 
            "[Khôi phục dữ liệu] Đã khôi phục {$modelName}: {$title} về trạng thái cũ",
            null, 
            $model->toArray()
        );

        return redirect()->route('backend.history.show', $history->id)
                        ->with('success', 'Đã khôi phục dữ liệu cũ thành công!');
    }
}