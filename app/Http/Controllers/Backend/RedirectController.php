<?php

namespace App\Http\Controllers\Backend;

use App\Enums\StatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\RedirectRequest;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $redirects = Redirect::orderByDesc('created_at')->paginate(10);

        return view('backend.redirect.index', [
            'redirects' => $redirects
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $statusCode = StatusCode::cases();

        return view('backend.redirect.create', [
            'statusCode' => $statusCode,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RedirectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RedirectRequest $request)
    {
        $redirect = Redirect::create([
            'redirect_from' => $request->redirect_from,
            'redirect_to' => $request->redirect_to,
            'redirect_code' => $request->redirect_code,
            'user_id' => Auth::user()->id,
        ]);

        session()->flash('backend.toast.success', 'Lưu thông chuyển hướng url thành công!');

        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.redirect.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.redirect.edit', ['id' => $redirect->id]);
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
        $redirect = Redirect::find($id);
        if ($redirect == null) {
            return redirect()->route('backend.redirect.index');
        }

        $statusCode = StatusCode::cases();
        return view('backend.redirect.edit', [
            'redirect' => $redirect,
            'statusCode' => $statusCode,
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
        $redirect = Redirect::find($id);
        $redirect->redirect_from = $request->redirect_from;
        $redirect->redirect_to = $request->redirect_to;
        $redirect->redirect_code = $request->redirect_code;
        $redirect->save();

        session()->flash('backend.toast.success', 'Lưu thông chuyển hướng url thành công!');
        if ($request->input('submit') == 'save') {
            return redirect()->route('backend.redirect.index');
        }

        if ($request->input('submit') == 'save_continue') {
            return redirect()->route('backend.redirect.edit', ['id' => $id]);
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
        Redirect::where('id', $id)->delete();

        session()->flash('backend.toast.success', 'Xóa chuyển hướng url thành công!');
        return redirect()->route('backend.redirect.index');
    }
}
