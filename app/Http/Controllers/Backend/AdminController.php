<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changePass()
    {
        return view('backend.admin.change-password');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAttributeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePass(ChangePasswordRequest $request)
    {
        try {
            $userLogin = Auth::user()->id;
            $user = User::find($userLogin);
            $user->password = Hash::make($request->password);
            $user->save();

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/backend');

            session()->flash('backend.toast.success', 'Thay đổi mật khẩu thành công!');
        } catch (\Exception $ex) {
            session()->flash('backend.toast.error', 'Thay đổi mật khẩu không thành công!');
            return redirect()->route('backend.admin.change-pass');
        }
    }
}
