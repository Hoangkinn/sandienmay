<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DropzoneController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function uploadImage(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:png,jpg,git|max:2048'
    //     ]);

    //     $images = [];
    //     $fileName = $request->file->getClientOriginalName();
    //     $request->file->move(public_path('storage/photos/' . Auth::user()->id), $fileName);

    //     $image = new ProductImage();
    //     $image->name = $fileName;
    //     $image->url = '/storage/photos/' . Auth::user()->id .'/'. $fileName;
    //     $image->thumb_url = '/storage/photos/' . Auth::user()->id .'/'. $fileName;
    //     if ($request->content_type == 'post') {
    //         $image->image_tag = '<img src="'. $image->url .'"/>';
    //     }

    //     array_push($images, $image);
    //     return $images;
    // }

    // public function deleteImage(Request $request)
    // {
    //     $path = public_path($request->url);
    //     if (file_exists($path)) {   
    //         unlink($path);
    //     }

    //     return [
    //         'error' => true
    //     ];
    // }
    public function uploadImage(Request $request)
    {
        // Ảnh chèn vào nội dung bài viết (blade khai báo image_scope=content ở data-route)
        // chỉ nhận tối đa 100KB; ảnh bộ sưu tập sản phẩm/dự án vẫn giữ mức cũ.
        $maxKb = $request->query('image_scope') === 'content' ? 100 : 5120;

        $request->validate([
            'file' => 'required|image|mimes:png,jpg,jpeg,gif,webp|max:' . $maxKb
        ], [
            'file.max' => 'Ảnh không được vượt quá ' . $maxKb . 'KB. Vui lòng giảm dung lượng ảnh rồi tải lại.',
            'file.image' => 'File chọn không phải là ảnh.',
        ]);

        $images = [];
        $file = $request->file('file');

        // 1. Giữ nguyên định dạng ảnh gốc, chỉ đổi tên để tránh trùng lặp
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = time() . '_' . $originalName . '.' . $file->guessExtension();

        // 2. Định nghĩa thư mục lưu trữ
        $userFolder = 'storage/photos/' . Auth::user()->id;
        $uploadPath = public_path($userFolder);

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // 3. Lưu file gốc xuống đĩa, không re-encode
        $file->move($uploadPath, $fileName);

        // 4. Lưu thông tin ảnh vào Database y như cũ
        $image = new ProductImage();
        $image->name = $fileName;
        $image->url = '/' . $userFolder . '/' . $fileName;
        $image->thumb_url = '/' . $userFolder . '/' . $fileName;
        
        if ($request->content_type == 'post') {
            $image->image_tag = '<img src="'. $image->url .'"/>';
        }

        array_push($images, $image);
        return $images;
    }

    public function deleteImage(Request $request)
    {
        $path = public_path($request->url);
        if (file_exists($path)) {   
            unlink($path);
        }

        return [
            'error' => false
        ];
    }
}
