<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Sắp xếp theo order tăng dần, nếu trùng order thì lấy mới nhất lên trước
        $banners = Banner::orderBy('order', 'asc')
                         ->orderByDesc('created_at')
                         ->paginate(10);
                         
        return view('backend.banner.index', [
            'banners' => $banners
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
        $targetList = Target::cases();
        return view('backend.banner.create', [
            'statusList' => $statusList,
            'targetList' => $targetList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BannerRequest $request)
    {
        $fileName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('storage'), $fileName);

        $fileMobileName = null;
        if ($request->hasFile('image_mobile')) {
            $fileMobileName = time() . '_mobile_' . uniqid() . '.' . $request->image_mobile->extension();
            $request->image_mobile->move(public_path('storage'), $fileMobileName);
        }

        $banner = Banner::create([
            'title' => $request->title,
            'image' => $fileName,
            'image_mobile' => $fileMobileName,
            'link' => $request->link,
            'position' => $request->position,
            'target' => $request->target,
            'order' => $request->order,
            'status' => $request->status,
            'user_id' => Auth::user()->id,
        ]);

        session()->flash('backend.toast.success', 'Lưu banner thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.banner.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.banner.edit', ['id' => $banner->id]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $statusList = Status::cases();
        $targetList = Target::cases();
        $banner = Banner::find($id);

        if ($banner == null) {
            return redirect()->route('backend.banner.index');
        }

        return view('backend.banner.edit', [
            'banner' => $banner,
            'statusList' => $statusList,
            'targetList' => $targetList
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
        $banner = Banner::find($id);
        
        if ($banner == null) {
            return redirect()->route('backend.banner.index');
        }

        $banner->title = $request->title;
        $banner->link = $request->link;
        $banner->position = $request->position;
        $banner->target = $request->target;
        $banner->order = $request->order;
        $banner->status = $request->status;

        if ($request->hasFile('image')) {
            if (file_exists(public_path('storage/' . $banner->image))) {
                @unlink(public_path('storage/' . $banner->image));
            }

            $fileName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('storage'), $fileName);
            $banner->image = $fileName;
        }

        if ($request->hasFile('image_mobile')) {
            if ($banner->image_mobile && file_exists(public_path('storage/' . $banner->image_mobile))) {
                @unlink(public_path('storage/' . $banner->image_mobile));
            }

            $fileMobileName = time() . '_mobile_' . uniqid() . '.' . $request->image_mobile->extension();
            $request->image_mobile->move(public_path('storage'), $fileMobileName);
            $banner->image_mobile = $fileMobileName;
        }

        $banner->save();

        session()->flash('backend.toast.success', 'Cập nhật banner thành công!');
        return redirect()->route('backend.banner.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     $banner = Banner::find($id);
    //     $banner->delete();

    //     session()->flash('backend.toast.success', 'Xóa banner thành công!');
    //     return redirect()->route('backend.banner.index');
    // }
    public function destroy($id)
    {
        $banner = Banner::find($id);
        
        if ($banner) {
            if (file_exists(public_path('storage/' . $banner->image))) {
                @unlink(public_path('storage/' . $banner->image));
            }
            
            if ($banner->image_mobile && file_exists(public_path('storage/' . $banner->image_mobile))) {
                @unlink(public_path('storage/' . $banner->image_mobile));
            }

            $banner->delete();
            session()->flash('backend.toast.success', 'Xóa banner thành công!');
        }

        return redirect()->route('backend.banner.index');
    }
}
