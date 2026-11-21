<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionCategory;
use App\Models\PromotionProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promotions = Promotion::orderByDesc('created_at')->paginate(10);

        return view('backend.promotion.index', [
            'promotions' => $promotions
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

        $products = Product::orderByDesc('created_at')
            ->where('status', Status::ACTIVED->value)
            ->get();

        return view('backend.promotion.create', [
            'statusList' => $statusList,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PromotionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PromotionRequest $request)
    {
        $promotion = Promotion::create([
            'title' => $request->title,
            'apply_for_child' => $request->apply_for_child,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'content' => $request->content,
            'status' => $request->status,
            'user_id' => Auth::user()->id,
        ]);

        if ($request->category_ids) {
            foreach($request->category_ids as $category_id) {
                PromotionCategory::create([
                    'promotion_id' => $promotion->id,
                    'category_id' => $category_id,
                ]);
            }
        }

        if ($request->product_ids) {
            foreach($request->product_ids as $product_id) {
                PromotionProduct::create([
                    'promotion_id' => $promotion->id,
                    'product_id' => $product_id,
                ]);
            }
        }

        session()->flash('backend.toast.success', 'Lưu thông tin khuyến mại thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.promotion.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.promotion.edit', ['id' => $promotion->id]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $promotion = Promotion::find($id);
        if ($promotion == null) {
            return redirect()->route('backend.promotion.index');
        }

        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();
        $promotionCategories = PromotionCategory::where('promotion_id', $id)->get();

        $products = Product::orderByDesc('created_at')
            ->where('status', Status::ACTIVED->value)
            ->get();
        $promotionProducts = PromotionProduct::where('promotion_id', $id)->get();

        return view('backend.promotion.edit', [
            'promotion' => $promotion,
            'categories' => $categories,
            'promotionCategories' => $promotionCategories,
            'promotionProducts' => $promotionProducts,
            'products' => $products,
            'statusList' => $statusList
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $promotion = Promotion::find($id);
        $promotion->title = $request->title;
        $promotion->apply_for_child = $request->apply_for_child;
        $promotion->from_date = $request->from_date;
        $promotion->to_date = $request->to_date;
        $promotion->content = $request->content;
        $promotion->status = $request->status;
        $promotion->save();

        PromotionCategory::where('promotion_id', $promotion->id)->delete();
        if ($request->category_ids) {
            foreach($request->category_ids as $category_id) {
                PromotionCategory::create([
                    'promotion_id' => $promotion->id,
                    'category_id' => $category_id,
                ]);
            }
        }

        PromotionProduct::where('promotion_id', $promotion->id)->delete();
        if ($request->product_ids) {
            foreach($request->product_ids as $product_id) {
                PromotionProduct::create([
                    'promotion_id' => $promotion->id,
                    'product_id' => $product_id,
                ]);
            }
        }

        session()->flash('backend.toast.success', 'Lưu thông tin khuyến mại thành công!');
        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.promotion.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.promotion.edit', ['id' => $id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        PromotionProduct::where('promotion_id', $id)->delete();
        PromotionCategory::where('promotion_id', $id)->delete();
        Promotion::where('id', $id)->delete();

        session()->flash('backend.toast.success', 'Xóa khuyến mại thành công!');
        return redirect()->route('backend.promotion.index');
    }
}
