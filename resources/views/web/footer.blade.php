@php use App\Helpers\GetFeUrlHelper; @endphp

<div class="newsletter">
    <div class="container">
        <h4 class="newsletter-title">Đăng ký email nhận tin khuyến mãi</h4>
        <form class="newsletter-form" method="post" action="{{ route('frontend.newsletter.store') }}"
            data-onsuccess="resetNewsletterForm">
            <input type="email" name="email" required placeholder="Nhập email của bạn">
            <button type="submit">Đăng ký</button>
        </form>
    </div>
</div>

<footer class="footer footer-v2">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                {!! $ho_tro_khach_hang !!}
            </div>

            <div class="footer-col">
                <h4 class="footer-title">Về chúng tôi</h4>
                <div class="footer-content">
                    <ul>
                        <li><a class="footer-link" href="{{ url('/gioi-thieu') }}" aria-label="Giới thiệu" rel="nofollow">Giới thiệu</a></li>
                        <li><a class="footer-link" href="{{ url('/lien-he') }}" aria-label="Liên hệ" rel="nofollow">Liên hệ</a></li>
                        <li><a class="footer-link" href="{{ url('/tin-tuc-may') }}" aria-label="Tin tức">Tin tức</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-col">
                {!! $danh_muc_san_pham !!}
            </div>

            <div class="footer-col">
                <h4 class="footer-title">Kết nối với chúng tôi</h4>
                <div class="footer-content">
                    <div class="social">
                        @if ($linkYoutube)
                            <a href="{{ $linkYoutube }}" target="_blank" rel="nofollow" aria-label="Youtube">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.9-.5-5.7a3 3 0 0 0-2.1-2.1C18.6 3.7 12 3.7 12 3.7s-6.6 0-8.4.5A3 3 0 0 0 1.5 6.3C1 8.1 1 12 1 12s0 3.9.5 5.7a3 3 0 0 0 2.1 2.1c1.8.5 8.4.5 8.4.5s6.6 0 8.4-.5a3 3 0 0 0 2.1-2.1C23 15.9 23 12 23 12zM9.8 15.5v-7l6.1 3.5-6.1 3.5z"/></svg>
                            </a>
                        @endif
                        @if ($linkFb)
                            <a href="{{ $linkFb }}" target="_blank" rel="nofollow" aria-label="Facebook">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5h1.65V4.6a22 22 0 0 0-2.4-.12c-2.35 0-3.95 1.43-3.95 4.07v2.27H7.6V14h2.8v8h3.1z"/></svg>
                            </a>
                        @endif
                        @if ($linkTiktok)
                            <a href="{{ $linkTiktok }}" target="_blank" rel="nofollow" aria-label="Tiktok">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.8a4.5 4.5 0 0 1-1-2.8h-3v12.4a2.5 2.5 0 1 1-2.5-2.5c.26 0 .5.04.75.1V9.8a5.6 5.6 0 0 0-.75-.05 5.5 5.5 0 1 0 5.5 5.5V9.3a7.4 7.4 0 0 0 4.3 1.37V7.6a4.5 4.5 0 0 1-3.25-1.8z"/></svg>
                            </a>
                        @endif
                        @if ($linkZalo)
                            <a href="{{ $linkZalo }}" target="_blank" rel="nofollow" aria-label="Zalo"><span class="zalo-text">Zalo</span></a>
                        @endif
                    </div>

                    <h4 class="footer-title mt">Chấp nhận thanh toán</h4>
                    <ul class="pay-list d-flex flex-wrap gap-10">
                        @php $banks = ['card1', 'card2', 'card3', 'card4'] @endphp
                        @foreach ($banks as $bank)
                            <li>
                                <img src="{{ asset('images/' . $bank . '.png') }}" width="92" height="51" alt="{{ $bank }}">
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-legal">
        <div class="container">
            <div class="legal-grid">
                <div>{!! $footer_1 !!}</div>
                <div>{!! $footer_2 !!}</div>
                <div>{!! $footer_3 !!}</div>
            </div>
        </div>
    </div>

    <div class="footer-copyright">
        <div class="container">
            <p>{!! $giay_chung_nhan !!}</p>
            <div class="cert-badges">
                @if ($linkBoCongThuong)
                    <a href="{{ $linkBoCongThuong }}" target="_blank" rel="nofollow" aria-label="Bộ công thương">
                        <img src="{{ asset('images/bct.png') }}" width="102" height="32" alt="Đã thông báo bộ công thương">
                    </a>
                @endif
                <a href="//www.dmca.com/Protection/Status.aspx?ID=2fede14b-64a8-42d3-b131-c7bd13499f2f" title="DMCA.com Protection Status" class="dmca-badge">
                    <img src="https://images.dmca.com/Badges/dmca_protected_sml_120l.png?ID=2fede14b-64a8-42d3-b131-c7bd13499f2f" alt="DMCA.com Protection Status" width="120" height="34">
                </a>
            </div>
        </div>
    </div>
</footer>

@push('scripts')
<script>
    function resetNewsletterForm(rs, form) {
        form.reset();
    }
</script>
@endpush
