<?php

namespace App\Services;

use App\Models\History;

class ActivityLogService
{
    public static function log($action, $modelType, $modelId, $description, $oldData = null, $newData = null)
    {
        try {
            if (auth()->check()) {
                History::create([
                    'user_id'     => auth()->id(),
                    'action'      => $action,
                    'model_type'  => $modelType,
                    'model_id'    => $modelId,
                    'description' => $description,
                    'old_data'    => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                    'new_data'    => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                    'ip_address'  => request()->ip(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi ghi lịch sử hoạt động: ' . $e->getMessage());
        }
    }
}