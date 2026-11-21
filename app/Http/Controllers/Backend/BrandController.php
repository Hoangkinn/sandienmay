<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Helpers\UploadImageHelper;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderByDesc('id')->get();
        return view('backend.brand.index', compact('brands'));
    }

    public function create()
    {
        // Lấy danh sách danh mục (type=product) để hiển thị trong form chọn
        $categories = Category::where('type', 'product')
                          ->where(function($query) {
                              $query->whereNull('parent_id')
                                    ->orWhere('parent_id', 0);
                          })
                          ->get();
        return view('backend.brand.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'order', 'is_active']);

        // Xử lý upload ảnh
        // if ($request->hasFile('image')) {
        //     $data['image'] = $request->file('image')->store('brands', 'public');
        // }
        if ($request->hasFile('image')) {
            // Ném thẳng vào thư mục vật lý public/storage/brands để khớp với các phần khác;
            // tên ảnh được slug không dấu, không khoảng trắng
            $filename = UploadImageHelper::store($request->file('image'), 'storage/brands', false)['name'];
            $data['image'] = 'brands/' . $filename;
        }

        $brand = Brand::create($data);

        // Đồng bộ danh mục
        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('backend.brand.index')->with('success', 'Thêm thương hiệu thành công!');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $categories = Category::where('type', 'product')
                          ->where(function($query) {
                              $query->whereNull('parent_id')
                                    ->orWhere('parent_id', 0);
                          })
                          ->get();
        return view('backend.brand.edit', compact('brand', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        
        $data = $request->only(['name', 'order', 'is_active']);

        // if ($request->hasFile('image')) {
        //     // Xóa ảnh cũ nếu có
        //     if ($brand->image) {
        //         Storage::disk('public')->delete($brand->image);
        //     }
        //     $data['image'] = $request->file('image')->store('brands', 'public');
        // }
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($brand->image && file_exists(public_path('storage/' . $brand->image))) {
                @unlink(public_path('storage/' . $brand->image));
            }

            $filename = UploadImageHelper::store($request->file('image'), 'storage/brands', false)['name'];
            $data['image'] = 'brands/' . $filename;
        }

        $brand->update($data);
        $brand->categories()->sync($request->categories ?? []);

        return redirect()->route('backend.brand.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        // Kiểm tra nếu có sản phẩm nào thuộc thương hiệu này
        if ($brand->products()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa vì thương hiệu đang có sản phẩm!');
        }

        // Xóa ảnh và detach khỏi danh mục trước khi xóa brand
        if ($brand->image) Storage::disk('public')->delete($brand->image);
        $brand->categories()->detach();
        $brand->delete();

        return redirect()->route('backend.brand.index')->with('success', 'Đã xóa thành công!');
    }
}