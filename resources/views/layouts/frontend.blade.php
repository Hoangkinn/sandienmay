<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @stack('preload')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        let csrf = '{{ csrf_token() }}';
    </script>
    <meta name="description" content="@yield('description')" />
    <meta name="keywords" content="@yield('keywords')" />
    <meta name="robots" content="{{ config('app.no_index') ? 'noindex, nofollow' : 'index, follow' }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <x-setting fieldKey="favicon"></x-setting>
    <link rel="canonical" href="@yield('canonical')">
    <link type="text/css" rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/fontawesome-all.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/jquery.custom-scrollbar.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/responsive.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/custom.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css-new/dist/base.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css-new/dist/layout.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css-new/dist/cart.min.css') }}" />
    @stack('frontend-css')
    <x-setting fieldKey="customize_header"></x-setting>
    @stack('scripts-header')
    @stack('schema')
</head>

<body>

    @include('frontend.header')

    <main>
        @yield('content')
    </main>

    @include('frontend.footer')

    <section class="tool-home">
        <div class="tool-list">
            <div class="hotline-popup-wrapper tool-item">
                <input type="checkbox" id="toggle-hotline-popup" class="hotline-popup-checkbox" hidden>
                <label for="toggle-hotline-popup" title="Hotline" class="cursor-pointer">
                    <img src="{{ asset('images/phone.svg') }}" width="21" height="21" alt="icon phone">
                </label>
            </div>

            @if($link_mess)
                <a href="{{ $link_mess }}" target="_blank" rel="nofollow" class="tool-item">
                    <img src="{{ asset('images/mess.svg') }}" width="22" height="22" alt="icon messengers">
                </a>
            @endif

            @if($linkZalo)
                <a href="{{ $linkZalo }}" target="_blank" rel="nofollow" class="tool-item">
                    <img src="{{ asset('images/zalo.svg') }}" width="21" height="20" alt="icon zalo">
                </a>
            @endif
            <button id="scrollToTopBtn" class="tool-item map-trigger-btn" onclick="scrollToTop()">
                <img src="{{ asset('images/icon2.svg') }}" width="21" height="16" alt="icon tool">
            </button>
        </div>
    </section>

            <div class="hotline-overlay">
                <label for="toggle-hotline-popup" class="overlay-close-bg"></label>
                
                <div class="hotline-popup-content">
                    <label for="toggle-hotline-popup" class="popup-close-btn">&times;</label>
                    
                    <h3 class="popup-title">Tư vấn mua hàng</h3>
                    
                    <div class="popup-meta-info">
                        <span>Hỗ trợ nhanh 24/7: <a href="tel:{{ $phoneQuickSp }}" class="fw-bold text-dark text-decoration-none">{{ $phoneQuickSp }}</a></span>
                        <span class="ms-lg-4">Email: <a href="{{ $mail }}" class="text-dark text-decoration-none">{{ $mail }}</a></span>
                    </div>

                    <div class="popup-grid-regions">
                        
                        <div class="region-box">
                            <h4 class="region-title">Phía Bắc</h4>
                            
                            <div class="phone-row-item">
                                <div class="phone-number">{{$phoneHn}}</div>
                                <div class="action-buttons">
                                    <a href="tel:{{$phoneHn}}" class="btn-action btn-call"><i class="icon-phone"></i> Gọi ngay</a>
                                    <a href="{{$zalo_hn}}" target="_blank" class="btn-action btn-zalo"><i class="icon-zalo"></i> Zalo chat</a>
                                </div>
                            </div>
                            
                            <div class="phone-row-item">
                                <div class="phone-number">{{$phoneHn}}</div>
                                <div class="action-buttons">
                                    <a href="tel:{{$phoneHn}}" class="btn-action btn-call"><i class="icon-phone"></i> Gọi ngay</a>
                                    <a href="{{$zalo_hn}}" target="_blank" class="btn-action btn-zalo"><i class="icon-zalo"></i> Zalo chat</a>
                                </div>
                            </div>
                        </div>

                        <div class="region-box">
                            <h4 class="region-title">Phía Nam</h4>
                            
                            <div class="phone-row-item">
                                <div class="phone-number">{{$phoneHcm}}</div>
                                <div class="action-buttons">
                                    <a href="tel:{{$phoneHcm}}" class="btn-action btn-call"><i class="icon-phone"></i> Gọi ngay</a>
                                    <a href="{{$zalo_hcm}}" target="_blank" class="btn-action btn-zalo"><i class="icon-zalo"></i> Zalo chat</a>
                                </div>
                            </div>
                            
                            <div class="phone-row-item">
                                <div class="phone-number">{{$phoneHcm2}}</div>
                                <div class="action-buttons">
                                    <a href="tel:{{$phoneHcm2}}" class="btn-action btn-call"><i class="icon-phone"></i> Gọi ngay</a>
                                    <a href="{{$zalo_hcm2}}" target="_blank" class="btn-action btn-zalo"><i class="icon-zalo"></i> Zalo chat</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

    <script type="text/javascript" src="{{ asset('js/jquery-1.12.0.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lazyload.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.custom-scrollbar.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/elevateZoom.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/main.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js-new/fe/main.min.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var owl_slider = $(".slider");
            owl_slider.owlCarousel({
                items: 1,
                loop: $('.slider .item').size() > 1 ? true : false,
                mouseDrag: $('.slider .item').size() > 1 ? true : false,
                dots: $('.slider .item').size() > 1 ? true : false,
                nav: false,
                autoplay: true,
                autoplayTimeout: 5000,
            });

            var owl_slider_doi_tac = $(".slider-doi-tac-list");
            owl_slider_doi_tac.owlCarousel({
                loop: true,
                dots: false,
                nav: false,
                autoplay: true,
                autoplayTimeout: 5000,
                margin: 20,
                responsive: {
                    0: {
                        items: 3,
                    },
                    400: {
                        items: 6,
                    },
                    550: {
                        items: 9,
                    },
                    992: {
                        items: 12,
                    }
                }
            });

            $(".home-product-tax-scroll").each(function() {
                var height = $(this).outerHeight();
                if (height > 538) {
                    $(this).customScrollbar();
                }
            });
        });

        
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkbox = document.getElementById("toggle-hotline-popup");
            const overlay = document.querySelector(".hotline-overlay");

            if (checkbox && overlay) {
                checkbox.addEventListener("change", function () {
                    if (this.checked) {
                        overlay.classList.add("is-active");
                        document.body.style.overflow = "hidden"; 
                    } else {
                        overlay.classList.remove("is-active");
                        document.body.style.overflow = "";
                    }
                });

                function closePopup() {
                    checkbox.checked = false;
                    overlay.classList.remove("is-active");
                    document.body.style.overflow = "";
                }

                const closeTriggers = overlay.querySelectorAll('.overlay-close-bg, .popup-close-btn');
                closeTriggers.forEach(trigger => {
                    trigger.addEventListener("click", function (e) {
                        e.preventDefault();
                        closePopup();
                    });
                });

                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape" && overlay.classList.contains("is-active")) {
                        closePopup();
                    }
                });
            }
        });
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            const scrollBtn = document.getElementById("scrollToTopBtn");

            if (scrollBtn) {
                window.addEventListener("scroll", function () {
                    if (window.scrollY > 300) {
                        scrollBtn.style.display = "flex"; 
                    } else {
                        scrollBtn.style.display = "block";
                    }
                });
            }
        });
    </script>
</body>

</html>
