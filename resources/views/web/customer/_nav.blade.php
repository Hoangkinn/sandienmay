@php $activeNav = $activeNav ?? ''; @endphp
<div class="cust-card">
    <div class="cust-nav-title">Tài khoản của tôi</div>
    <ul class="cust-nav">
        <li>
            <a href="{{ route('frontend.customer.account') }}" class="{{ $activeNav === 'account' ? 'is-active' : '' }}">
                Thông tin của tôi
            </a>
        </li>
        <li>
            <a href="{{ route('frontend.customer.orders') }}" class="{{ in_array($activeNav, ['orders', 'order']) ? 'is-active' : '' }}">
                Đơn hàng của tôi
            </a>
        </li>
    </ul>
    <form method="POST" action="{{ route('frontend.customer.logout') }}" class="cust-logout" data-skip-ajax>
        @csrf
        <button type="submit" class="cust-btn is-ghost">Đăng xuất</button>
    </form>
</div>
