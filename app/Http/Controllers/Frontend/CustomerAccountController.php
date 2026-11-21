<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerAccountController extends FrontendController
{
    private function customer()
    {
        return Auth::guard('customers')->user();
    }

    public function index(): View
    {
        return view('web.customer.account', [
            'customer' => $this->customer(),
            'orderCount' => $this->customer()->orders()->count(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric', 'digits:10'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Họ tên không được để trống',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Định dạng phải là số',
            'phone.digits' => 'Số điện thoại phải là 10 chữ số',
        ]);

        $this->customer()->update([
            'name' => $validated['name'],
            'phone_number' => $validated['phone'],
            'address' => $validated['address'] ?? null,
        ]);

        $request->session()->flash('success', 'Đã cập nhật thông tin tài khoản.');

        return redirect()->route('frontend.customer.account');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Mật khẩu hiện tại không được để trống',
            'password.required' => 'Mật khẩu mới không được để trống',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp',
        ]);

        $customer = $this->customer();

        if (! Hash::check($validated['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $customer->update(['password' => Hash::make($validated['password'])]);

        $request->session()->flash('success', 'Đã đổi mật khẩu.');

        return redirect()->route('frontend.customer.account');
    }
}
