<?php

namespace App\Http\Controllers\Backend;

use App\Enums\PostType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->name) {
            $pages = Post::where('name', 'like', '%' . $request->name . '%')
                ->where('type', PostType::PAGE->value)
                ->orderByDesc('created_at')
                ->paginate(10);
        } else {
            $pages = Post::orderByDesc('created_at')
                ->where('type', PostType::PAGE->value)
                ->paginate(10);
        }

        return view('backend.page.index', [
            'pages' => $pages
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
        return view('backend.page.create', [
            'statusList' => $statusList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request)
    {
        $page = Post::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'status' => $request->status,
            'type' => PostType::PAGE->value,
            'seo_no_index' => $request->seo_no_index,
            'seo_title' => $request->seo_title,
            'seo_keywords' => $request->seo_keywords,
            'seo_description' => $request->seo_description,
            'seo_url_canonical' => $request->seo_url_canonical,
            'schema' => $request->schema,
            'user_id' => Auth::user()->id,
        ]);

        session()->flash('backend.toast.success', 'Lưu bài viết thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.page.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.page.edit', ['id' => $page->id]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $statusList = Status::cases();
        $page = Post::find($id);

        if ($page == null) {
            return redirect()->route('backend.page.index');
        }

        return view('backend.page.edit', [
            'page' => $page,
            'statusList' => $statusList
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // 1. Tìm bản ghi theo ID
        $page = Post::findOrFail($id);

        // 2. Gán dữ liệu mới
        $page->title = $request->title;
        $page->slug = $request->slug;
        $page->content = $request->content;
        $page->status = $request->status;
        $page->seo_no_index = $request->seo_no_index;
        $page->seo_title = $request->seo_title;
        $page->seo_keywords = $request->seo_keywords;
        $page->seo_description = $request->seo_description;
        $page->seo_url_canonical = $request->seo_url_canonical;
        $page->schema = $request->schema;
        
        // 3. Lưu lại bằng save() để kích hoạt sự kiện updated của Trait ghi log
        $page->save();

        session()->flash('backend.toast.success', 'Lưu bài viết thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.page.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.page.edit', ['id' => $id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Post::find($id);
        $page->delete();

        return redirect()->route('backend.page.index');
    }
}
