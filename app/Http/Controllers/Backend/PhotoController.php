<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\UploadImageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,gif,webp|max:100'
        ], [
            'image.max' => 'Ảnh chèn vào bài viết không được vượt quá 100KB. Vui lòng giảm dung lượng ảnh rồi tải lại.',
            'image.image' => 'File chọn không phải là ảnh.',
        ]);

        // Tên ảnh giữ dạng thuần slug (không prefix thời gian). Ảnh đã có trên đĩa theo từng byte
        // được dùng lại, thay vì bắt admin đổi tên rồi nhân bản thêm file trùng.
        $stored = UploadImageHelper::store($request->image, 'storage', true, false);

        return url($stored['url']);
    }
}
