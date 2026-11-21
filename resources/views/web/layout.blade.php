@php
$ver = config('app.ver_asset');
@endphp
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- Google Fonts da bo: Inter self-host trong base.css (@font-face), xem deploy/checklist-upload-demo.md vong 9. --}}

        {!! \App\Helpers\SeoHelper::render() !!}
        {{-- <meta name="robots" content="noindex, nofollow" /> --}}
        @stack('preload')

        <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">

        <link rel="preload" href="/css-new/fonts/icomoon.ttf?v=2026" as="font" type="font/ttf" crossorigin="anonymous">
        @php
            // Chuỗi fetch nối tiếp: main.min.js -> loadJs('CustomGlide.min.js') -> loadJs('glide.min.js').
            // loadJs tái dùng đúng ?v của main.min.js nên preload ba URL này ở head biến chuỗi thành song song.
            $mainJsPath = 'js-new/fe/main.min.js';
            $mainJsVer = file_exists(public_path($mainJsPath)) ? filemtime(public_path($mainJsPath)) : time();
        @endphp
        <link rel="preload" href="{{ asset($mainJsPath . '?v=' . $mainJsVer) }}" as="script">
        <link rel="preload" href="{{ asset('js-new/fe/CustomGlide.min.js?v=' . $mainJsVer) }}" as="script">
        <link rel="preload" href="{{ asset('js-new/fe/glide.min.js?v=' . $mainJsVer) }}" as="script">
        <link rel="stylesheet" href="{{ asset('css-new/dist/fancybox.min.css') }}" media="print" onload="this.media='all'" />

        @inlineCss('css-new/dist/base.min.css')
        @inlineCss('css-new/dist/layout.min.css')
        @stack('page-css')
    @php
        $delayed_header = $customize_header;
        if (strpos($delayed_header, 'googletagmanager.com') !== false) {
            $delayed_header = preg_replace(
                '/<script\s+(?:async\s+)?src="https:\/\/www\.googletagmanager\.com\/gtag\/js\?id=([^"]+)"\s*>\s*<\/script>/i',
                '<script>
                (function() {
                    var loaded = false;
                    function loadGtag() {
                        if (loaded) return;
                        loaded = true;
                        var script = document.createElement("script");
                        script.src = "https://www.googletagmanager.com/gtag/js?id=$1";
                        script.async = true;
                        document.head.appendChild(script);
                        // Remove event listeners
                        triggerEvents.forEach(function(e) {
                            window.removeEventListener(e, loadGtag, { passive: true });
                        });
                    }
                    var triggerEvents = ["touchstart", "scroll", "mousemove", "keydown", "click"];
                    triggerEvents.forEach(function(e) {
                        window.addEventListener(e, loadGtag, { passive: true });
                    });
                })();
                </script>',
                $delayed_header
            );
        }
    @endphp
    {!! $delayed_header !!}
    @yield('css') 
    <base href="{{ config('app.url') }}">
    <script>
        let csrf = '{{ csrf_token() }}';
    </script>
    {{-- @stack('schema') --}}
    @stack('scripts-header')
    @php 
    $organizationSchema = [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "@id" => url('/') . '#organization',
        "name" => config('app.name'),
        "url" => url('/'),
        "logo" => [
            "@type" => "ImageObject",
            "url" => $logo,
        ],
        "contactPoint" => [
            [
                "@type" => "ContactPoint",
                "telephone" => $phoneHn ?: $phoneHn2,
                "contactType" => "customer service",
                "areaServed" => "VN",
                "availableLanguage" => ["vi"],
            ],
            [
                "@type" => "ContactPoint",
                "telephone" => $phoneHn ?: $phoneHn2,
                "contactType" => "sales",
                "areaServed" => "VN",
                "availableLanguage" => ["vi"],
            ],
        ],
    ];

    $webSiteSchema = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "@id" => url('/') . '#website',
        "url" => url('/'),
        "name" => config('app.name'),
        "inLanguage" => "vi-VN",
        "publisher" => [
            "@type" => "Organization",
            "@id" => url('/') . '#organization',
        ],
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => url('/tim-kiem') . '?s={search_term_string}',
            "query-input" => "required name=search_term_string",
        ],
    ];

    $localBusinessSchema = [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "@id" => url('/') . '#localbusiness',
        "name" => "Sàn điện máy",
        "image" => $logo,
        "telephone" => $phoneHn ?: $phoneHn2,
        "priceRange" => "100,000 VND - 900,000,000 VND",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $addressHn,
            "addressLocality" => "Hà Nội",
            "addressRegion" => "Hà Nội",
            "postalCode" => "100000",
            "addressCountry" => "VN"
        ],
        "url" => url('/'),
        // Kết nối với Organization để Google hiểu đây là một thực thể thống nhất
        "parentOrganization" => [
            "@id" => url('/') . '#organization'
        ]
    ];


    @endphp
    {{-- Kiểm tra xem trang chủ có nhập schema tùy chỉnh hay không --}}
    @php
        $isHomePage = request()->routeIs('frontend.page.home') || request()->path() === '/' || request()->path() === '';
        $homepageCustomSchema = null;
        if ($isHomePage) {
            $homepageCustomSchema = \App\Models\Setting::where('setting_name', 'homepage_schema')->value('setting_value');
        }
        $homepageCustomSchema = is_string($homepageCustomSchema) ? trim($homepageCustomSchema) : null;
    @endphp

    {{-- Nếu có schema trang chủ -> Định nghĩa section default-schema rỗng để ẩn 3 cái cũ đi --}}
    @if(!empty($homepageCustomSchema))
        @section('default-schema')
        @endsection
    @else
        @section('default-schema')
            <script type="application/ld+json">
            {!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
            <script type="application/ld+json">
            {!! json_encode($webSiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
            <script type="application/ld+json">
            {!! json_encode($localBusinessSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
            </script>
        @endsection
    @endif

    {{-- Hiển thị default-schema --}}
    @yield('default-schema')
    
    @stack('schema')

    {{-- In ra Schema tùy chỉnh của trang chủ nếu có --}}
    @if(!empty($homepageCustomSchema))
        @if(str_starts_with($homepageCustomSchema, '<script'))
            {!! $homepageCustomSchema !!}
        @else
            <script type="application/ld+json">
            {!! $homepageCustomSchema !!}
            </script>
        @endif
    @endif

    <meta name="google-site-verification" content="q0iKxATRgwT1zZrJQ7ZmIQYyYBdnJmXGWLvOhC1iZz8" />
</head>

<body class="m-0 @yield('body-class')">
@include('web/header')
@auth
    @php
        $currentPath = request()->path();
        $editUrl = route('backend.dashboard');

        $product = \App\Models\Product::where('slug', $currentPath)->first();
        if ($product) {
            $editUrl = route('backend.product.edit', $product->id);
        } else {
            $post = \App\Models\Post::where('slug', $currentPath)->first();
            if ($post) {
                $editUrl = route('backend.post.edit', $post->id);
            } else {
                $category = \App\Models\Category::where('slug', $currentPath)->first();
                if ($category) {
                    // Truyền đủ 2 tham số type và id theo đúng URI: backend/category/{type}/{id}/edit
                    $editUrl = route('backend.category.edit', ['type' => $category->type, 'id' => $category->id]);
                }
            }
        }
    @endphp

  
    <div id="admin-quick-bar" style="background: rgb(29 68 216); color: #fff; padding: 6px 15px; font-size: 14px; position: fixed; top: 0; left: 0; right: 0; z-index: 99999; transition: transform 0.3s ease; transform: translateY(0);">
        <ul style="list-style: none; margin: 0; padding: 0; display: flex; align-items: center; gap: 25px;">
            <li>
                <a href="{{ route('backend.dashboard') }}" style="color: #fff; text-decoration: none; font-weight: bold;">Admin</a>
            </li>
            <li class="admin-dropdown" style="position: relative;">
                    <button type="button" style="background: none; border: none; color: #fff; cursor: pointer; font: inherit; padding: 0;">Thêm mới +</button>            <div class="admin-dropdown-content" style="display: none; position: absolute; background: #fff; box-shadow: 0px 4px 8px rgba(0,0,0,0.2); top: 100%; left: 0; min-width: 150px; z-index: 10;">
                    <a href="{{ route('backend.post.create') }}" style="color: #333; padding: 8px 12px; display: block; text-decoration: none;">Thêm tin tức</a>
                    <a href="{{ route('backend.product.create') }}" style="color: #333; padding: 8px 12px; display: block; text-decoration: none;">Thêm sản phẩm</a>
                </div>
            </li>
            <li>
                <a href="{{ $editUrl }}" target="_blank" style="color: #fff; text-decoration: none;">Sửa nhanh</a>
            </li>
            <li>
                <a href="{{ route('backend.setting.index') }}" target="_blank" style="color: #fff; text-decoration: none;">Sửa Schema</a>
            </li>
            <li>
                <a href="{{ route('backend.clear.cache') }}" onclick="return confirm('Bạn có muốn xóa cache hệ thống không?')" style="color: #fff; text-decoration: none;">Xóa cache</a>
            </li>
        </ul>
    </div>
    <div style="height: 35px;"></div>

    {{-- Script nhỏ để hỗ trợ hover menu Thêm mới + --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropdown = document.querySelector('.admin-dropdown');
            if(dropdown) {
                dropdown.addEventListener('mouseenter', () => {
                    dropdown.querySelector('.admin-dropdown-content').style.display = 'block';
                });
                dropdown.addEventListener('mouseleave', () => {
                    dropdown.querySelector('.admin-dropdown-content').style.display = 'none';
                });
            }
        });
    </script>
@endauth
<main class="main">
    @yield('content')   
</main>

@include('web/footer')
<style>
    .icon-contact:before {
        content: "\e9016";
    }
</style>
<button id="scrollToTopBtn" class="scroll-top-btn" type="button" aria-label="Lên đầu trang" onclick="scrollToTop()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
        stroke-linejoin="round" aria-hidden="true">
        <path d="M12 19V5M5.5 11.5L12 5l6.5 6.5" />
    </svg>
</button>

{{-- Widget lien he noi (Zalo / Messenger / Hotline). Du lieu lay tu m_setting_fields
     qua View composer trong AppServiceProvider::boot. --}}
@if ($linkZalo || $link_mess || $phoneHn)
    <div class="lh-wrap" id="lhWrap">
        <div class="lh-panel" id="lhPanel">
            <div class="lh-head">
                <div>
                    <strong>Tư vấn &amp; bán hàng</strong>
                    <span>Chọn kênh phù hợp với bạn</span>
                </div>
                <button class="lh-close" type="button" aria-label="Đóng khung liên hệ">
                    <svg viewBox="3 3 18 18" fill="none" stroke="currentColor" stroke-width="2.4"
                        stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            <div class="lh-body">
                @if ($linkZalo)
                    <a class="lh-row" href="{{ $linkZalo }}" target="_blank" rel="noopener nofollow">
                        <span class="lh-ic is-zalo">
                            <svg viewBox="0 -0.5 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2.4c-5.3 0-9.6 3.8-9.6 8.6 0 2.5 1.3 4.8 3.3 6.3l-.7 3.3 3.6-1.6c1.1.3 2.2.4 3.4.4 5.3 0 9.6-3.8 9.6-8.6S17.3 2.4 12 2.4zm-3.9 4.7h5.4l-4 6.4h3.9v1.7H6.3l4-6.4H8.1V7.1z" />
                            </svg>
                        </span>
                        <span class="lh-txt">
                            <strong>Chat Zalo</strong>
                            <em>{{ $phoneHn ?: 'Trao đổi trực tiếp' }}</em>
                        </span>
                        <span class="lh-go" aria-hidden="true">&rsaquo;</span>
                    </a>
                @endif
                @if ($link_mess)
                    <a class="lh-row" href="{{ $link_mess }}" target="_blank" rel="noopener nofollow">
                        <span class="lh-ic is-mess">
                            <svg viewBox="0.06 0.11 23.88 23.88" fill="currentColor" aria-hidden="true">
                                <path d="M12 2.5c-5.2 0-9.3 3.9-9.3 8.9 0 2.8 1.3 5.3 3.5 6.9v3.3l3.2-1.8c.9.2 1.8.4 2.6.4 5.2 0 9.3-3.9 9.3-8.8S17.2 2.5 12 2.5zm1 11.6l-2.5-2.7-3.4 2.7 3.3-4.9 2.6 2.6 3.3-2.6-3.3 4.9z" />
                            </svg>
                        </span>
                        <span class="lh-txt">
                            <strong>Nhắn Messenger</strong>
                            <em>Trao đổi trực tiếp trên Facebook</em>
                        </span>
                        <span class="lh-go" aria-hidden="true">&rsaquo;</span>
                    </a>
                @endif
                @if ($phoneHn)
                    <a class="lh-row" href="tel:{{ $phoneHn }}">
                        <span class="lh-ic is-call">
                            <svg viewBox="1.56 0.18 22.09 22.09" fill="currentColor" aria-hidden="true">
                                <path d="M19.8 15.4l-3-1.4a1.6 1.6 0 0 1-1.9.35 12 12 0 0 1-5.3-5.3 1.6 1.6 0 0 1 .3-1.95l1.4-2.95L9.9 2.6A1.6 1.6 0 0 0 8.3 1.6H4.9A1.7 1.7 0 0 0 3.2 3.5 19.6 19.6 0 0 0 20.5 20.8a1.7 1.7 0 0 0 1.7-1.7v-3.4a1.6 1.6 0 0 0-1.1-1.5z"
                                    transform="scale(.92) translate(1 1)" />
                            </svg>
                        </span>
                        <span class="lh-txt">
                            <strong>Gọi hotline</strong>
                            <em>{{ $phoneHn }}</em>
                        </span>
                        <span class="lh-go" aria-hidden="true">&rsaquo;</span>
                    </a>
                @endif
            </div>
            @if ($addressHn)
                <div class="lh-foot">Showroom: {{ $addressHn }}</div>
            @endif
        </div>
        <button class="lh-fab" type="button" aria-expanded="false" aria-controls="lhPanel"
            aria-label="Liên hệ tư vấn">
            <span class="lh-dot" aria-hidden="true"></span>
            <svg viewBox="-1.13 0.33 26.25 26.25" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" aria-hidden="true">
                <path d="M4 15v-3a8 8 0 0 1 16 0v3" />
                <rect x="2.5" y="14" width="4.5" height="6.5" rx="2.2" fill="currentColor" stroke="none" />
                <rect x="17" y="14" width="4.5" height="6.5" rx="2.2" fill="currentColor" stroke="none" />
                <path d="M19.5 20.5c0 1.4-1.6 2.4-4 2.4" />
            </svg>
        </button>
        <span class="lh-label">Liên hệ tư vấn</span>
    </div>
@endif



{{-- <script type="text/javascript" src="{{ asset('/js-new/fe/main.min.js') }}?v={{ $ver }}"></script> --}}
{{-- defer: script cuối body vẫn bị trình duyệt tính là chặn hiển thị. Inline script ở
     @stack('scripts') gọi Common.loadJs() phải bọc DOMContentLoaded. --}}
<script type="text/javascript" src="{{ asset($mainJsPath . '?v=' . $mainJsVer) }}" defer></script>
<script src="https://images.dmca.com/Badges/DMCABadgeHelper.min.js" defer></script>

@stack('lazyContent')
@stack('scripts')
</body>


    
</html>
