<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AttributeType;
use App\Enums\CategoryType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Attribute::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $attributes = $query->get();
        $attributeTypes = AttributeType::cases();
        return view('backend.attribute.index', [
            'attributeTypes' => $attributeTypes,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();
        return view('backend.attribute.create', [
            'statusList' => $statusList,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAttributeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttributeRequest $request)
    {
        try {
            DB::beginTransaction();
            $fileName = '';
            if ($request->image) {
                $fileName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('storage'), $fileName);
            }

            $attribute = Attribute::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'image' => $fileName,
                'parent_id' => $request->parent_id,
                'is_search' => $request->is_search,
                'is_display' => $request->is_display,
                'position' => $request->position,
                'status' => $request->status,
                'user_id' => Auth::user()->id,
            ]);

            if ($attribute) {
                $categoryIds = $request->input('categories_id', []);
                foreach ($categoryIds as $categoryId) {
                    CategoryAttribute::create([
                        'attribute_id' => $attribute->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }
            DB::commit();
            session()->flash('backend.toast.success', 'Thêm mới thuộc tính thành công!');

            if ($request->input('submit') == 'save') {
                return redirect()->route('backend.attribute.index');
            }

            if ($request->input('submit') == 'save_continue') {
                return redirect()->route('backend.attribute.edit', ['id' => $attribute->id]);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            session()->flash('backend.toast.eror', 'Thêm mới sản phẩm không thành công!');
            return redirect()->route('backend.attribute.create');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attribute = Attribute::find($id);
        if ($attribute == null) {
            return redirect()->route('backend.attribute.index');
        }

        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();

        return view('backend.attribute.edit', [
            'attribute' => $attribute,
            'categories' => $categories,
            'statusList' => $statusList
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attribute = Attribute::with('categories')->find($id);
        if ($attribute == null) {
            return redirect()->route('backend.attribute.index');
        }

        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();

        return view('backend.attribute.edit', [
            'attribute' => $attribute,
            'categories' => $categories,
            'statusList' => $statusList
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAttributeRequest  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttributeRequest $request, $id)
    {

        try {
            DB::beginTransaction();
            $attribute = Attribute::find($id);
            $attribute->name = $request->name;
            $attribute->slug = $request->slug;
            if ($request->image) {
                $fileName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('storage'), $fileName);
                $attribute->image = $fileName;
            }

            $attribute->status = $request->status;
            $attribute->parent_id = $request->parent_id;
            $attribute->is_search = $request->is_search;
            $attribute->is_display = $request->is_display;
            $attribute->position = $request->position;
            $attribute->save();

            if ($attribute) {
                CategoryAttribute::where('attribute_id', $attribute->id)->delete();
                $categoryIds = $request->input('categories_id', []);
                foreach ($categoryIds as $categoryId) {
                    CategoryAttribute::create([
                        'attribute_id' => $attribute->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }

            DB::commit();
            session()->flash('backend.toast.success', 'Cập nhật thuộc tính thành công!');

            if ($request->input('submit') == 'save') {
                return redirect()->route('backend.attribute.index');
            }

            if ($request->input('submit') == 'save_continue') {
                return redirect()->route('backend.attribute.edit', ['id' => $attribute->id]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger('update attribute error : ' . $e->getMessage());
            logger($e->getFile() . ':' . $e->getLine());
            session()->flash('backend.toast.error', 'Cập nhật thuộc tính không thành công!');
            return redirect()->route('backend.attribute.edit', ['id' => $attribute->id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $attribute = Attribute::findOrFail($id);
            ProductAttribute::where('attribute_id', $id)->delete();
            AttributeOption::where('attribute_id', $id)->delete();
            $attribute->delete();
        });

        session()->flash('backend.toast.success', 'Xóa thuộc tính thành công!');
        return redirect()->route('backend.attribute.index');
    }
}
