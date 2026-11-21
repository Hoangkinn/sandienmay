<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Http\Requests\ReviewRequest;
use App\Models\Banner;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::paginate(10);
        return view('backend.review.index', [
            'reviews' => $reviews
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.review.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReviewRequest $request)
    {
        $fileName = time() . '.' . $request->avatar->extension();
        $request->avatar->move(public_path('storage'), $fileName);

        $review = Review::create([
            'name' => $request->name,
            'avatar' => $fileName,
            'content' => $request->content,
        ]);

        session()->flash('backend.toast.success', 'Lưu đánh giá thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.review.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.review.edit', ['id' => $review->id]);
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
        $review = Review::find($id);

        if ($review == null) {
            return redirect()->route('backend.review.index');
        }

        return view('backend.review.edit', [
            'review' => $review,
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
        $review = Review::find($id);
        $review->name = $request->name;
        $review->content = $request->content;
        if ($request->avatar) {
            $fileName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage'), $fileName);
            $review->avatar = $fileName;
        }

        $review->save();

        session()->flash('backend.toast.success', 'Cập nhật đánh giá thành công!');
        return redirect()->route('backend.review.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = Review::find($id);
        $review->delete();

        session()->flash('backend.toast.success', 'Xóa đánh giá thành công!');
        return redirect()->route('backend.review.index');
    }
}
