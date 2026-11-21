<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $videos = Video::orderByDesc('created_at')->paginate(10);

        return view('backend.video.index', [
            'videos' => $videos
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

        return view('backend.video.create', [
            'statusList' => $statusList,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreVideoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVideoRequest $request)
    {
        $video = Video::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'url' => $request->url,
            'image' => null,
            'content' => $request->content,
            'order' => $request->order,
            'status' => $request->status,
            'is_feature' => $request->is_feature,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'seo_url_canonical' => $request->seo_url_canonical,
            'seo_no_index' => $request->seo_no_index,
            'product_id' => $request->product_id,
            'category_id' => $request->category_id,
            'created_id' => Auth::user()->id,
            'updated_id' => Auth::user()->id,
        ]);

        session()->flash('backend.toast.success', 'Lưu thông tin video thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.video.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.video.edit', ['id' => $video->id]);
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
        $video = Video::find($id);
        if ($video == null) {
            return redirect()->route('backend.video.index');
        }

        $statusList = Status::cases();
        $categories = Category::orderByDesc('created_at')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null)
            ->get();

        $products = Product::orderByDesc('created_at')
            ->where('status', Status::ACTIVED->value)
            ->get();

        return view('backend.video.edit', [
            'video' => $video,
            'categories' => $categories,
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
        $video = Video::find($id);
        if ($video == null) {
            return redirect()->route('backend.video.index');
        }

        $video->title = $request->title;
        $video->slug = $request->slug;
        $video->url = $request->url;
        $video->image = null;
        $video->content = $request->content;
        $video->order = $request->order;
        $video->status = $request->status;
        $video->is_feature = $request->is_feature;
        $video->seo_title = $request->seo_title;
        $video->seo_keywords = $request->seo_keywords;
        $video->seo_description = $request->seo_description;
        $video->seo_url_canonical = $request->seo_url_canonical;
        $video->seo_no_index = $request->seo_no_index;
        $video->product_id = $request->product_id;
        $video->category_id = $request->category_id;
        $video->updated_id = Auth::user()->id;
        $video->save();

        session()->flash('backend.toast.success', 'Lưu thông tin video thành công!');
        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.video.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.video.edit', ['id' => $id]);
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
        Video::where('id', $id)->delete();

        session()->flash('backend.toast.success', 'Xóa video thành công!');
        return redirect()->route('backend.video.index');
    }
}
