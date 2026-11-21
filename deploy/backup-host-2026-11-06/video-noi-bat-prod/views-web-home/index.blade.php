@php use App\Helpers\GetFeUrlHelper; @endphp
@extends('web.layout')

@section('body-class') home-v2 @endsection

@push('preload')
    @if (!empty($bannersTop[0]['imageMobile']))
        <link rel="preload" as="image" href="{{ url($bannersTop[0]['imageMobile']) }}" media="(max-width: 992px)" fetchpriority="high">
        @php
            // <img> chi mang duoc mot bo width/height, ma <source> mobile co ti le rieng
            // -> ep aspect-ratio theo dung breakpoint cua <source> de o chong khong doi
            // khi anh mobile decode.
            $mobSize = @getimagesize(public_path(ltrim(parse_url(url($bannersTop[0]['imageMobile']), PHP_URL_PATH) ?? '', '/')));
        @endphp
        @if ($mobSize)
            <style>@media (max-width: 992px) { .home-v2 .hero-banner picture > img { aspect-ratio: {{ $mobSize[0] }} / {{ $mobSize[1] }}; } }</style>
        @endif
    @endif
    @if (!empty($bannersTop[0]['image']))
        {{-- Không có ảnh mobile thì preload cho MỌI viewport: để media="(min-width: 993px)"
             là mobile phải chờ parser tìm thấy ảnh LCP trong slider. --}}
        <link rel="preload" as="image" href="{{ url($bannersTop[0]['image']) }}" @if (!empty($bannersTop[0]['imageMobile'])) media="(min-width: 993px)" @endif fetchpriority="high">
    @endif
@endpush

@push('page-css')
    @inlineCss('css-new/dist/home-v2.min.css')
@endpush

@section('content')
    {{-- Sidebar danh mục (desktop) + banner: thay cho dải banner full ngang cũ --}}
    <section class="home-hero" aria-label="Danh mục sản phẩm và banner khuyến mãi">
        <div class="container">
            <div class="home-hero-grid">
                <aside class="home-side" aria-label="Danh mục sản phẩm">
                    <x-fe.menu mode="sidebar"></x-fe.menu>
                </aside>

                <div class="home-hero-main">
                    {{-- Banner chính --}}
                    @if (!empty($bannersTop))
                        <div class="hero-banner">
                            <div class="glide">
                                <div class="glide__track " data-glide-el="track">
                                    <div class="glide__slides">
                                        @foreach ($bannersTop as $k => $banner)
                                            @php $isFirst = $k === 0; @endphp
                                            @if ($banner['url'])
                                                <a class="glide__slide banner-link" href="{{ $banner['url'] }}" title="{{ $banner['name'] }}" aria-label="{{ $banner['name'] }}">
                                            @else
                                                <div class="glide__slide banner-link">
                                            @endif
                                                <picture>
                                                    @if (!empty($banner['imageMobile']))
                                                        <source media="(max-width: 992px)" srcset="{{ url($banner['imageMobile']) }}">
                                                    @endif
                                                    <img @if ($isFirst)
                                                            src="{{ url($banner['image']) }}" fetchpriority="high" loading="eager"
                                                        @else
                                                            data-lazy="{{ url($banner['image']) }}" src="/img-new/icons/16x9.webp" loading="lazy"
                                                        @endif
                                                        alt="{{ $banner['name'] }}" @imgAttrs(url($banner['image']), 1920, 500)>
                                                </picture>
                                            @if ($banner['url'])
                                                </a>
                                            @else
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                @if (count($bannersTop) > 1)
                                    <div class="glide__bullets text-center" data-glide-el="controls[nav]">
                                        @foreach ($bannersTop as $k => $banner)
                                            <button class="glide__bullet" data-glide-dir="={{ $k }}" aria-label="Banner {{ $k + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 3 banner phụ --}}
                    @if (!empty($bannersMid))
                        <div class="promo-section" aria-label="Banner khuyến mãi">
                            <div class="promo-grid d-grid">
                                @foreach ($bannersMid as $banner)
                                    @php $inner = '<img src="' . url($banner['image']) . '" alt="' . $banner['name'] . '" class="responsive-img" ' . \App\Providers\AppServiceProvider::imgAttrs(url($banner['image']), 680, 227) . ' loading="lazy">'; @endphp
                                    @if ($banner['url'])
                                        <a href="{{ $banner['url'] }}" class="promo-item" title="{{ $banner['name'] }}">{!! $inner !!}</a>
                                    @else
                                        <div class="promo-item">{!! $inner !!}</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Banner ngang giữa trang (fix cứng) --}}
    <section class="wide-banner" aria-label="Banner khuyến mãi">
        <div class="container">
            <div class="banner-link">
                {{-- JPEG goc 1216x204 = 294 KB; webp 26.9 KB (desktop) va 14.2 KB (780w cho mobile).
                     <img> van giu JPEG de trinh duyet khong ho tro webp. --}}
                <picture>
                    <source type="image/webp" srcset="{{ asset('images/flash-sale-m.webp') }} 780w, {{ asset('images/flash-sale.webp') }} 1216w"
                        sizes="(max-width: 992px) 100vw, 1216px">
                    <img src="{{ asset('images/flash-sale.jpg') }}" alt="Khuyến mãi" class="responsive-img" @imgAttrs(asset('images/flash-sale.jpg'), 1216, 447) loading="lazy">
                </picture>
            </div>
        </div>
    </section>

    {{-- Khuyến mãi --}}
    @if (!empty($saleProducts))
        <section class="sale-section" aria-label="Sản phẩm khuyến mãi">
            <div class="container">
                <div class="sale-box">
                    <div class="glide" data-perview="2" data-pc-perview="5" data-bp-1200="4" data-bp-1024="4" data-autoplay="0">
                        <div class="glide__track" data-glide-el="track">
                            <div class="glide__slides">
                                @foreach ($saleProducts as $product)
                                    <div class="glide__slide">
                                        <x-fe.products :products="[$product]" :preload="false" :viewCart="false" :viewSoldQuantity="true"></x-fe.products>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div data-glide-el="controls">
                            <span class="carousel-btn" data-glide-dir="<" role="button" aria-label="Xem trước">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
                            </span>
                            <span class="carousel-btn" data-glide-dir=">" role="button" aria-label="Xem sau">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Block danh mục --}}
    @foreach ($catHome as $i)
        @php
            $iconPath = str_replace('/storage/', '', (string) ($i['icon'] ?? ''));
            $iconData = json_decode($iconPath, true);
            $iconMain = is_array($iconData) ? ($iconData['main'] ?? '') : $iconPath;
            $imagePath = str_replace('/storage/', '', (string) ($i['image'] ?? ''));

            if ($iconMain !== '' && $iconMain !== '[]') {
                $catBanner = asset('storage/' . $iconMain);
            } elseif ($imagePath !== '' && $imagePath !== '[]') {
                $catBanner = asset('storage/' . $imagePath);
            } else {
                $catBanner = asset('images/banner-sp.png');
            }
        @endphp
        <section class="cat-block" data-parent-wrapper aria-label="Danh mục {{ $i['name'] }}">
            <div class="container">
                <div class="cat-block-box">
                    <h2 class="cat-block-title">
                        <a href="{{ $i['url'] }}">{{ $i['name'] }}</a>
                    </h2>

                    <div class="cat-block-body">
                        <a href="{{ $i['url'] }}" class="cat-block-banner">
                            <img src="{{ $catBanner }}" alt="{{ $i['name'] }}" class="responsive-img" @imgAttrs($catBanner, 256, 424) loading="lazy">
                        </a>

                        <div class="cat-block-main">
                            <div class="cat-pills">
                                {{-- Hiện tối đa 5 danh mục có ảnh, còn lại ẩn --}}
                                @foreach (collect($i['children'])->filter(fn($c) => !empty($c['image']))->take(5) as $i2)
                                    <span class="cat-pill home-cat-item"
                                        data-cat-id="{{ $i2['id'] }}"
                                        data-parent-id="{{ $i['id'] }}"
                                        data-cat-url="{{ $i2['url'] }}">
                                        <img src="{{ $i2['image'] }}" alt="icon danh mục {{ $i2['name'] }}" width="22" height="22" loading="lazy">
                                        <span class="cat-pill-name">{{ $i2['name'] }}</span>
                                    </span>
                                @endforeach
                                <a href="{{ $i['url'] }}" class="cat-pill cat-pill-all">Xem tất cả</a>
                            </div>

                            <div class="glide carousel-wrap position-relative" id="home-cat-products-slider-{{ $i['id'] }}" data-perview="2" data-pc-perview="4" data-bp-1200="4" data-bp-1024="3" data-autoplay="0">
                                <div data-glide-el="track" class="glide__track">
                                    <div class="glide__slides cat-products-track" id="home-cat-products-{{ $i['id'] }}">
                                        @foreach ($i['products'] as $product)
                                            <div class="glide__slide">
                                                <x-fe.products :products="[$product]" :viewCart="false" :viewSoldQuantity="true"></x-fe.products>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div data-glide-el="controls">
                                    <span class="carousel-btn light" data-glide-dir="<" role="button" aria-label="Xem trước">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
                                    </span>
                                    <span class="carousel-btn light" data-glide-dir=">" role="button" aria-label="Xem sau">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    {{-- Báo chí nói về chúng tôi (fix cứng) --}}
    @php
        $pressLogos = [
            ['image' => 'bao1.jpg', 'name' => 'VTC News'],
            ['image' => 'bao2.jpg', 'name' => 'Báo điện tử Dân Trí'],
            ['image' => 'bao3.jpg', 'name' => 'An ninh Thủ đô'],
            ['image' => 'bao4.jpg', 'name' => '24h Công nghệ'],
            ['image' => 'bao5.jpg', 'name' => 'CafeF'],
            ['image' => 'bao6.jpg', 'name' => 'Báo Tiền Phong'],
        ];
    @endphp
    <section class="press-section" aria-label="Báo chí nói về chúng tôi">
        <div class="container">
            <h2 class="section-title">BÁO CHÍ NÓI VỀ CHÚNG TÔI</h2>
            <div class="glide carousel-wrap press-carousel" data-perview="2" data-pc-perview="5" data-bp-1200="5" data-bp-1024="4" data-autoplay="0">
                <div data-glide-el="track" class="glide__track">
                    <div class="glide__slides">
                        @foreach ($pressLogos as $logo)
                            <div class="glide__slide">
                                <a href="#" class="press-item" aria-label="{{ $logo['name'] }}">
                                    <img src="{{ asset('images/' . $logo['image']) }}" alt="{{ $logo['name'] }}"
                                        width="194" height="108" loading="lazy">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div data-glide-el="controls">
                    <span class="carousel-btn light" data-glide-dir="<" role="button" aria-label="Xem trước">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </span>
                    <span class="carousel-btn light" data-glide-dir=">" role="button" aria-label="Xem sau">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Đối tác - Khách hàng (fix cứng) --}}
    <section class="partner-section" aria-label="Đối tác khách hàng">
        <div class="container">
            <div class="partner-box">
                <h2 class="section-title">Đối tác - Khách hàng</h2>
                <div class="partner-grid">
                    {{-- logo.svg thực chất là ảnh raster nhúng base64 (~49KB); dùng chung logo-m.png với header để browser táiuse bản 7KB --}}
                    @for ($k = 0; $k < 12; $k++)
                        <div class="partner-item"><img src="{{ asset('images/logo-m.png') }}" alt="Đối tác" width="100" height="70" loading="lazy"></div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    {{-- Video --}}
    @if (!empty($videos))
        <section class="video-section" aria-label="Video nổi bật">
            <div class="container">
                <h2 class="section-title center">VIDEO NỔI BẬT</h2>
                <div class="video-layout">
                    @php $featured = $videos[0] ?? null; @endphp
                    @if ($featured)
                        <a href="{{ $featured['url'] }}" class="video-featured" data-video="{{ $featured['youtubeId'] }}">
                            <span class="video-thumb">
                                <img src="{{ $featured['image'] }}" alt="{{ $featured['title'] }}" loading="lazy">
                                <i class="play-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></i>
                            </span>
                            <span class="video-caption">{{ $featured['title'] }}</span>
                        </a>
                    @endif
                    <div class="video-list">
                        @foreach (array_slice($videos, 1, 3) as $item)
                            <a href="{{ $item['url'] }}" class="video-row" data-video="{{ $item['youtubeId'] }}">
                                <span class="video-thumb">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                    <i class="play-icon small"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></i>
                                </span>
                                <span class="video-text">{{ $item['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Tin tức --}}
    @if (!empty($posts))
        <section class="news-section" aria-label="Tin tức">
            <div class="container">
                <div class="partner-box">
                    <div class="news-head">
                        <h2 class="section-title">TIN TỨC MÁY MÓC</h2>
                        <a href="/tin-tuc-may" class="view-all">Xem tất cả
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                    </div>
                    <div class="news-grid">
                        @foreach (array_slice($posts, 0, 4) as $post)
                            <a href="{{ $post['url'] }}" class="news-card">
                                <span class="news-thumb"><img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy"></span>
                                <h4 class="news-title">{{ $post['title'] }}</h4>
                                <span class="news-date">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    {{ \Carbon\Carbon::parse($post['date'])->format('d/m/Y') }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        window.syncCarouselNav = function (el) {
            const controls = el && el.querySelector('[data-glide-el="controls"]')
            if (!controls) return
            const perView = parseInt(el.dataset.pcPerview || el.dataset.perview || 0, 10)
            controls.style.display = el.querySelectorAll('.glide__slide').length > perView ? '' : 'none'
        }

        document.addEventListener('DOMContentLoaded', function () {
            Common.loadJs('CustomGlide.min.js').then(() => {
                CustomGlide.initAll()
                document.querySelectorAll('.carousel-wrap').forEach(syncCarouselNav)
            })
        })
    </script>
    <script type="text/javascript">
        document.addEventListener('click', function(e) {
            let item = e.target.closest('.home-cat-item');
            if (!item) return;

            let catId = item.dataset.catId;
            let parentId = item.dataset.parentId;
            let productsBox = document.querySelector('#home-cat-products-' + parentId);
            let catUrl = item.dataset.catUrl;

            item.parentElement.querySelectorAll('.home-cat-item')
                .forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            fetch(`${window.location.origin}/ajax/products-by-category/${catId}`)
                .then(res => res.json())
                .then(data => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data.html, 'text/html');
                    let productElements = Array.from(doc.querySelectorAll('.product-card'));

                    const sliderEl = document.querySelector('#home-cat-products-slider-' + parentId);

                    if (sliderEl) {
                        sliderEl.style.height = sliderEl.offsetHeight + 'px';
                        sliderEl.style.transition = 'opacity 0.15s ease, height 0.15s ease';
                        sliderEl.style.opacity = '0';
                    }

                    if (productElements.length === 0) {
                        productsBox.innerHTML = '<div class="w-100 text-center py-3">Không có sản phẩm nào</div>';
                        window.syncCarouselNav(sliderEl);
                        if (sliderEl) {
                            const instance = CustomGlide.getInstance(sliderEl);
                            if (instance) instance.destroy();
                            sliderEl.style.height = 'auto';
                            sliderEl.style.opacity = '1';
                        }
                        return;
                    }

                    let htmlContent = '';
                    productElements.forEach(el => {
                        htmlContent += `<div class="glide__slide">${el.outerHTML}</div>`;
                    });

                    productsBox.innerHTML = htmlContent;

                    if (sliderEl) {
                        const instance = CustomGlide.getInstance(sliderEl);
                        if (instance) {
                            try { instance.destroy(); } catch (err) {}
                        }

                        requestAnimationFrame(() => {
                            CustomGlide.create(sliderEl);
                            window.syncCarouselNav(sliderEl);

                            requestAnimationFrame(() => {
                                sliderEl.style.height = 'auto';
                                sliderEl.style.opacity = '1';
                            });

                            ModalDetailProduct.addEventClick();
                            // ModalCompareProduct.addEventClick(); // tam thoi bo so sanh
                        });
                    }
                });
        });
    </script>
@endpush
