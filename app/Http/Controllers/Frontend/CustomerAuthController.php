<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAuthController extends FrontendController
{
    private function loginRules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    private function loginMessages(): array
    {
        return [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Định dạng phải là email',
            'password.required' => 'Mật khẩu không được để trống',
        ];
    }

    public function showLogin(): View
    {
        if (Auth::guard('customers')->check()) {
            return redirect()->route('frontend.customer.account');
        }

        return view('web.customer.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate($this->loginRules(), $this->loginMessages());

        if (! Auth::guard('customers')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
        }

        if (Auth::guard('customers')->user()->isBlocked()) {
            Auth::guard('customers')->logout();

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tài khoản này đã bị khoá. Vui lòng liên hệ hotline để được mở lại.']);
        }

        $request->session()->regenerate();

        return redirect()->to(
            $request->session()->pull('customer.intended', route('frontend.customer.account'))
        );
    }

    public function showRegister(): View
    {
        if (Auth::guard('customers')->check()) {
            return redirect()->route('frontend.customer.account');
        }

        return view('web.customer.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('m_customers', 'email')],
            'phone' => ['required', 'numeric', 'digits:10'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Định dạng phải là email',
            'email.unique' => 'Email này đã có tài khoản, bạn hãy đăng nhập.',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Định dạng phải là số',
            'phone.digits' => 'Số điện thoại phải là 10 chữ số',
            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => 'actived',
        ]);

        // Nhận luôn đơn đã đặt trước đó kiểu khách vãng lai (PaymentController để customer_id = NULL),
        // khớp theo email — cùng email là cùng người, không cần OTP.
        $claimed = Order::whereNull('customer_id')
            ->where('customer_email', $customer->email)
            ->update(['customer_id' => $customer->id]);

        Auth::guard('customers')->login($customer);
        $request->session()->regenerate();

        $request->session()->flash('success', $claimed > 0
            ? "Tạo tài khoản thành công. Đã gắn {$claimed} đơn hàng cũ vào tài khoản của bạn."
            : 'Tạo tài khoản thành công.');

        return redirect()->route('frontend.customer.account');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customers')->logout();

        // Không session()->invalidate(): giỏ hàng (session('carts')) và mã giảm giá đang nằm ở đó.

        return redirect()->route('frontend.page.home');
    }
}
