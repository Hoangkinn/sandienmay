<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $file = $request->image;

        // 1. Lấy tên gốc, bỏ dấu tiếng Việt, viết liền, chuẩn SEO; giữ đúng đuôi ảnh gốc
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalName); // Chuyển "Máy Rửa Xe" thành "may-lua-xe"

        $fileName = $slugName . '.' . $file->guessExtension();
        $uploadPath = public_path('storage');
        
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $targetFile = $uploadPath . '/' . $fileName;

        // 2. Kiểm tra trùng lặp: Nếu file đã tồn tại thì báo lỗi yêu cầu đổi tên khác
        if (file_exists($targetFile)) {
            return response()->json([
                'message' => 'Tên ảnh đã tồn tại trên hệ thống! Vui lòng đổi tên file ảnh khác trước khi tải lên.'
            ], 422); // Mã 422 Unprocessable Entity để Editor hiển thị lỗi
        }

        // 3. Lưu file gốc xuống đĩa, không re-encode
        $file->move($uploadPath, $fileName);

        // 4. Trả về đường dẫn ảnh cho trình soạn thảo
        return url('/storage/' . $fileName);
    }
}