<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait ActivityLoggable
{
    public static function bootActivityLoggable()
    {
        // Danh sách các Model phụ không cần ghi log lịch sử
        $excludedModels = [
            'CategoryPost',
            'CategoryProduct',
            'ProductAttribute',
            'ProductImage',
            'ProjectImage',
            'ProjectVideo',
            'PromotionCategory',
            'PromotionProduct',
            // 2 bảng cấu hình phụ của danh mục cấp 1: lưu cả loạt dòng mỗi lần sửa danh mục
            // nên ghi log từng dòng chỉ làm nhiễu lịch sử.
            'CategoryPriceBand',
            'CategorySpec',
        ];

        // 1. Khi tạo mới (Create)
        static::created(function ($model) use ($excludedModels) {
            $modelName = class_basename($model);
            if (in_array($modelName, $excludedModels)) {
                return; // Bỏ qua nếu nằm trong danh sách loại trừ
            }

            $title = $model->title ?? $model->name ?? $model->email ?? ('ID: ' . $model->id);
            
            ActivityLogService::log(
                'create', 
                $modelName, 
                $model->id, 
                "Đã thêm mới {$modelName}: {$title}"
            );
        });

        // 2. Khi cập nhật (Update)
        static::updated(function ($model) use ($excludedModels) {
            $modelName = class_basename($model);
            if (in_array($modelName, $excludedModels)) {
                return;
            }

            $title = $model->title ?? $model->name ?? $model->email ?? ('ID: ' . $model->id);
            
            // Lấy dữ liệu thay đổi
            $oldAll = $model->getOriginal();
            $changes = $model->getChanges(); 

            $oldData = [];
            $newData = [];

            foreach ($changes as $key => $newValue) {
                if ($key == 'updated_at') continue;
                $oldData[$key] = $oldAll[$key] ?? null;
                $newData[$key] = $newValue;
            }
            
            ActivityLogService::log(
                'update', 
                $modelName, 
                $model->id, 
                "Đã cập nhật {$modelName}: {$title}",
                $oldData, // <-- Truyền dữ liệu cũ vào đây
                $newData  // <-- Truyền dữ liệu mới vào đây
            );
        });

        // 3. Khi xóa (Delete)
        static::deleting(function ($model) use ($excludedModels) {
            $modelName = class_basename($model);
            if (in_array($modelName, $excludedModels)) {
                return;
            }

            $title = $model->title ?? $model->name ?? $model->email ?? ('ID: ' . $model->id);
            
            ActivityLogService::log(
                'delete', 
                $modelName, 
                $model->id, 
                "Đã xóa {$modelName}: {$title}"
            );
        });
    }
}