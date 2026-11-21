@php use App\Helpers\GetFeUrlHelper; @endphp

<footer class="footer">
    <div class="container">
        <div class="footer-grid mb15">
            {{-- Cột 1: Thông tin liên hệ --}}
            <div class="footer-col">
                <img src="{{ asset('images/logo-footer.png') }}" width="209" height="62" alt="Logo Sàn điện máy" class="footer-logo">
                <p style="margin: 0px; font-size: 16px;">
                    <strong>Hà Nội:</strong> {!! $addressHn !!}<br>
                    <strong>Hotline:</strong> <a href="tel:{!! $phoneHn !!}" aria-label="Hotline Hà Nội">{!! $phoneHn !!}</a>
                </p>
                <p style="margin: 0px; font-size: 16px;">
                    <strong>TP. HCM:</strong> {{ $addressHcm }}<br>
                    <strong>Hotline:</strong> <a href="tel:{!! $phoneHcm !!}" aria-label="Hotline TP. HCM">{!! $phoneHcm !!}</a>
                </p>
                <p style="margin: 0px; font-size: 16px;"><strong>Email:</strong> {{ $mail }}</p>
                <p style="margin-bottom: 5px; margin-top: 5px; font-size: 16px;"><strong>Trụ sở ĐKKD:</strong> {{ $tru_so_dkkd }}</p>
                <p style="margin: 0px; font-size: 16px;"><strong>Mã số thuế:</strong> {{ $ma_so_thue }}</p>
            </div>

            {{-- Cột 2, 3, 4: Nội dung động (tận dụng các biến cũ) --}}
            <div class="footer-col">
                <div class="footer-content">
                    {!! $ho_tro_khach_hang !!}
                </div>
            </div>

            <div class="footer-col">
                <div class="footer-title">VỀ CHÚNG TÔI</div>
                <div class="footer-content">
                    <ul>
                        <li><a class="footer-link" href="{{ url('/gioi-thieu') }}" aria-label="Giới thiệu" rel="nofollow">Giới thiệu</a></li>
                        <li><a class="footer-link" href="{{ url('/lien-he') }}" aria-label="Liên hệ" rel="nofollow">Liên hệ</a></li>
                        <li><a class="footer-link" href="{{ url('/tin-tuc-may') }}" aria-label="Tin tức">Tin tức</a></li>
                        {{-- <li><a class="footer-link" href="{{ url('/tra-cuu-bao-hanh') }}" aria-label="Tra cứu bảo hành">Tra cứu bảo hành</a></li> --}}
                    </ul>
                </div>
            </div>

            <div class="footer-col">
                <div class="footer-content">
                    {!! $danh_muc_san_pham !!}
                </div>
            </div>
        </div>

        <div class="footer-grid">

            {{-- Kết nối & Chứng nhận --}}
            <div class="footer-col">
                <div class="footer-title">KẾT NỐI VỚI CHÚNG TÔI</div>
                <div class="footer-content">
                    <div class="social-icons">
                        @if($linkFb)<a href="{{ $linkFb }}" target="_blank" rel="nofollow" aria-label="Facebook"><img src="{{ asset('images/fb.png') }}" width="40" height="40" alt="Facebook"></a>@endif
                        @if($linkYoutube)<a href="{{ $linkYoutube }}" target="_blank" rel="nofollow" aria-label="Youtube"><img src="{{ asset('images/yt.svg') }}" width="40" height="40" alt="Youtube"></a>@endif
                        @if($linkTiktok)<a href="{{ $linkTiktok }}" target="_blank" rel="nofollow" aria-label="Tiktok"><img src="{{ asset('images/tiktok.png') }}" width="40" height="40" alt="Tiktok"></a>@endif
                    </div>
                    <div class="social-icons mt10">
                        @if($linkBoCongThuong)
                            <a href="{{ $linkBoCongThuong }}" target="_blank" rel="nofollow" aria-label="Bộ công thương"><img src="{{ asset('images/bct.svg') }}" width="102" height="32" alt="Bộ công thương"></a>
                        @endif
                        {{-- <a href="https://dmcaai.com" rel="nofollow" aria-label="DMCA"><img src="{{ asset('images/dmca.svg') }}" width="60" height="34" alt="DMCA"></a> --}}
                        <a href="//www.dmca.com/Protection/Status.aspx?ID=2fede14b-64a8-42d3-b131-c7bd13499f2f" title="DMCA.com Protection Status" class="dmca-badge" rel="nofollow"> 
                            <img src ="https://images.dmca.com/Badges/dmca_protected_sml_120m.png?ID=2fede14b-64a8-42d3-b131-c7bd13499f2f"  alt="DMCA.com Protection Status" />
                        </a>  
                        
                    </div>
                </div>
            </div>

            {{-- Phương thức thanh toán --}}
            <div class="footer-col">
                <div class="footer-title">PHƯƠNG THỨC THANH TOÁN</div>
                <div class="footer-content">
                    <ul class="footer-card d-flex gap10" style="flex-wrap: wrap;">
                        @php $banks = ['vpbank', 'bidv', 'techcom', 'vietcom', 'mb', 'acb'] @endphp
                        @foreach($banks as $bank)
                            <li>
                                <img src="{{ asset('img-new/banks/'.$bank.'.jpg') }}" width="65" height="40" alt="{{ $bank }}">
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© Bản quyền thuộc về Sàn điện máy - {!! $giay_chung_nhan !!}</p>
        </div>
    </div>
</footer>