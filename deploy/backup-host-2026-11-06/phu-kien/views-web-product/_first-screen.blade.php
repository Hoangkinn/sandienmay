
@php
    /*DTO $product*/
    /*bool $showFull*/
    $wishlist = session('wishlist', []);

    /**
     * Hàm helper cache ảnh từ URL về local server
     */
    if (!function_exists('get_cached_image')) {
        function get_cached_image($url, $folder = 'thumbs') {
            if (empty($url)) return '';
            
            // Nếu là ảnh nội bộ rồi thì bỏ qua
            if (str_starts_with($url, '/') || str_starts_with($url, asset(''))) {
                return $url;
            }

            $filename = md5($url) . '.jpg';
            $relativePath = 'uploads/cache/' . $folder . '/' . $filename;
            $absolutePath = public_path($relativePath);

            // Nếu file chưa tồn tại trên server thì tiến hành tải về
            if (!file_exists($absolutePath)) {
                $dir = dirname($absolutePath);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0755, true);
                }

                $content = @file_get_contents($url);
                if ($content !== false) {
                    @file_put_contents($absolutePath, $content);
                } else {
                    // Fallback nếu file_get_contents bị chặn (dùng cURL)
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $imageContent = curl_exec($ch);
                    curl_close($ch);

                    if ($imageContent) {
                        @file_put_contents($absolutePath, $imageContent);
                    } else {
                        return $url; // Trả về URL gốc nếu không tải được
                    }
                }
            }

            return asset($relativePath);
        }
    }
@endphp

@once
    @php
        $customSchema = null;
        if (isset($product)) {
            if (is_array($product)) {
                $customSchema = $product['schema'] ?? null;
            } elseif (is_object($product)) {
                $customSchema = $product->schema ?? null;
            }
        }
        $customSchema = is_string($customSchema) ? trim($customSchema) : null;
    @endphp

    {{-- Nếu admin có nhập custom schema -> Ẩn 3 schema cứng ở layout --}}
    @if(!empty($customSchema))
        @section('default-schema')
            {{-- Để trống để triệt tiêu 3 schema cứng --}}
        @endsection
    @endif

    @push('schema')
        @if(!empty($customSchema))
            {{-- Hiển thị schema tùy chỉnh của admin --}}
            @if(str_starts_with($customSchema, '<script'))
                {!! $customSchema !!}
            @else
                <script type="application/ld+json">
                {!! $customSchema !!}
                </script>
            @endif
        @else
            {{-- Nếu admin không nhập, tự động sinh Schema Product chuẩn SEO --}}
            @php
                $productName = $product['name'] ?? ($product->name ?? '');
                $productImage = $product['image'] ?? ($product->image ?? '');
                if (!$productImage) {
                    $productImage = $product['images'][0] ?? ($product->images[0] ?? '');
                }
                // Xử lý ảnh nếu là đường dẫn tương đối thì đổi thành tuyệt đối
                if ($productImage && !str_starts_with($productImage, 'http')) {
                    $productImage = asset($productImage);
                }
                
                $productDesc = strip_tags($product['description'] ?? ($product['content'] ?? ($product->description ?? ($product->content ?? ''))));
                $productSku = $product['sku'] ?? ($product->sku ?? ($product['id'] ?? ($product->id ?? '')));
                $productPrice = $product['price'] ?? ($product->price ?? 0);
                
                // Kiểm tra trạng thái còn hàng (mặc định coi như còn hàng nếu không có trường stock)
                $isInStock = $product['in_stock'] ?? ($product->in_stock ?? true);
                $availability = $isInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';

                // $productSchema = [
                //     '@context' => 'https://schema.org/',
                //     '@type' => 'Product',
                //     'name' => $productName,
                //     'image' => $productImage ? [$productImage] : [],
                //     'description' => mb_substr(trim($productDesc), 0, 300), // Cắt ngắn mô tả cho gọn Schema
                //     'sku' => (string) $productSku,
                //     'brand' => [
                //         '@type' => 'Brand',
                //         'name' => $product['brand_name'] ?? ($product->brand?->name ?? config('app.name'))
                //     ],
                //     'offers' => [
                //         '@type' => 'Offer',
                //         'url' => url()->current(),
                //         'priceCurrency' => 'VND',
                //         'price' => $productPrice,
                //         'availability' => $availability,
                //         'itemCondition' => 'https://schema.org/NewCondition',
                //         'seller' => [
                //             '@type' => 'Organization',
                //             'name' => config('app.name')
                //         ]
                //     ]
                // ];
                $productSchema = [
                    '@context' => 'https://schema.org/',
                    '@type' => 'Product',
                    'name' => $productName,
                    'image' => $productImage ? [$productImage] : [],
                    'description' => mb_substr(trim($productDesc), 0, 300),
                    'sku' => (string) $productSku,
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => $product['brand_name'] ?? ($product->brand?->name ?? config('app.name'))
                    ],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => url()->current(),
                        'priceCurrency' => 'VND',
                        'price' => $productPrice,
                        'availability' => $availability,
                        'itemCondition' => 'https://schema.org/NewCondition',
                        'seller' => [
                            '@type' => 'Organization',
                            'name' => config('app.name')
                        ],
                        // Fix lỗi thiếu shippingDetails
                        'shippingDetails' => [
                            '@type' => 'OfferShippingDetails',
                            'shippingRate' => [
                                '@type' => 'MonetaryAmount',
                                'value' => '0',
                                'currency' => 'VND'
                            ],
                            'shippingDestination' => [
                                '@type' => 'DefinedRegion',
                                'addressCountry' => 'VN'
                            ],
                            'deliveryTime' => [
                                '@type' => 'ShippingDeliveryTime',
                                'handlingTime' => [
                                    '@type' => 'QuantitativeValue',
                                    'minValue' => 0,
                                    'maxValue' => 1,
                                    'unitCode' => 'DAY'
                                ],
                                'transitTime' => [
                                    '@type' => 'QuantitativeValue',
                                    'minValue' => 1,
                                    'maxValue' => 3,
                                    'unitCode' => 'DAY'
                                ]
                            ]
                        ],
                        // Fix lỗi thiếu hasMerchantReturnPolicy
                        'hasMerchantReturnPolicy' => [
                            '@type' => 'MerchantReturnPolicy',
                            'applicableCountry' => 'VN',
                            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                            'merchantReturnDays' => 7,
                            'returnMethod' => 'https://schema.org/ReturnByMail',
                            'returnFees' => 'https://schema.org/FreeReturn'
                        ]
                    ]
                ];
            @endphp

            <script type="application/ld+json">
                {!! json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
    @endpush
@endonce
<style>
    .first-screen-row.is-popup-page {
        padding-bottom: 0;
    }

    .first-screen-row.is-detail-page {
        padding-bottom: 0;
    }

    .first-screen-cta {
        text-align: center;
        margin-bottom: 30px;
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .glideThumb .row {
        justify-content: flex-start !important;
        flex-wrap: nowrap;
        margin: 0;
        width: 100% !important;
    }

    .glideThumb .row > div {
        flex: 0 0 20% !important;
        max-width: 20% !important;
        width: 20% !important;
    }

    @media (max-width: 991px) {
        .first-screen-cta {
            position: relative;
        }

        .first-screen-row {
            padding-bottom: 0;
        }
    }
</style>

<div class="container g-0">
    <div class="product-container {{ $showFull ? 'is-detail' : '' }}">
        <div class="row g-0 g-lg-3 first-screen-row {{ $showFull ? 'is-detail-page' : 'is-popup-page' }}">
            <div class="col-lg-6">
    @if ($product['images'] || !empty($product['youtube_id']))
        @php
            $ytId = '';
            if (!empty($product['youtube_id'])) {
                $ytUrl = $product['youtube_id'];
                if (str_contains($ytUrl, 'watch?v=')) {
                    $ytId = strtok(substr($ytUrl, strpos($ytUrl, 'watch?v=') + 8), '&');
                } elseif (str_contains($ytUrl, 'youtu.be/')) {
                    $ytId = strtok(basename(parse_url($ytUrl, PHP_URL_PATH)), '?');
                } else {
                    $ytId = $ytUrl;
                }
            }
            $thumbIndex = 0;
            
            // Lấy và cache thumbnail YouTube về server
            $ytThumbUrl = !empty($ytId) ? get_cached_image("https://img.youtube.com/vi/{$ytId}/hqdefault.jpg", 'youtube') : '';
        @endphp

        <div class="bg-fff rounded-lg boxLibrary rounded-10 position-relative h-100">
            <div class="glideBig glide position-relative mb-2 text-center">
                <div class="overflow-hidden rounded" data-glide-el="track"
                    style="width: 97%; margin: 0 auto">
                    <div class="row gx-0 flex-nowrap">
                        
                        {{-- 1. CÁC SLIDE ẢNH SẢN PHẨM ĐỨNG TRƯỚC --}}
                        @if ($product['images'])
                            @foreach ($product['images'] as $k => $imgSource)
                                @php 
                                    $cachedImg = get_cached_image($imgSource, 'products'); 
                                @endphp
                                @pushif(! empty($preload) && !$k, 'preload')
                                <link rel="preload" as="image" href="{{ $cachedImg }}"
                                    fetchpriority="high">
                                @endpushif
                                <div>
                                    <div class="overflow-hidden">
                                        <img src="{{ $cachedImg }}"
                                            width="520" height="400" 
                                            @if (!empty($preload) && !$k) 
                                                loading="eager" decoding="sync" fetchpriority="high" 
                                            @else 
                                                loading="lazy" 
                                            @endif
                                            class="ratio-1 object-fit-contain rounded-5"
                                            alt="{{ $product['name'] ?? 'Ảnh sản phẩm' }} - {{ $k + 1 }}"
                                            style="width: 100%; height:auto; padding: 0">
                                    </div>
                                </div>
                                
                                {{-- PUSH THUMB ẢNH VÀO STACK --}}
                                @push('thumbItems')
                                    <div>
                                        <div class="glideThumbItem rounded-5 border border-D9D9D9 p-lg-5 p-3 rounded-5 thumb-cover product-image-sub @if($thumbIndex === 0) glide__slide--active @endif"
                                             data-thumb-index="{{ $thumbIndex }}">
                                            <img src="{{ $cachedImg }}"
                                                width="96" height="68"
                                                loading="lazy"
                                                alt="{{ $product['name'] ?? 'Ảnh sản phẩm' }} - Hình {{ $k + 1 }}"
                                                class="ratio-1 object-fit-contain rounded-5">
                                        </div>
                                    </div>
                                @endpush
                                @php $thumbIndex++; @endphp
                            @endforeach
                        @endif

                        {{-- 2. SLIDE LỚN CUỐI CÙNG: DÀNH CHO VIDEO (nằm sau cùng) --}}
                        @if (!empty($ytId) && $showFull)
                            <div>
                                <div class="overflow-hidden position-relative d-flex align-items-center justify-content-center" style="min-height: 400px; background: #000; border-radius: var(--bs-border-radius-lg);">
                                    <img src="{{ $ytThumbUrl }}"
                                        width="520" height="400" 
                                        class="ratio-1 object-fit-contain rounded-5 w-100 h-100"
                                        alt="{{ $product['name'] ?? 'Video sản phẩm' }}"
                                        style="opacity: 0.8;">
                                    <label for="toggle-yt-popup-{{ $product['id'] }}" 
                                        class="position-absolute top-50 start-50 translate-middle rounded-circle bg-dark bg-opacity-75 d-flex align-items-center justify-content-center cursor-pointer m-0"
                                        style="width: 64px; height: 64px; cursor: pointer;">
                                        <img src="{{ asset('/images/play.png') }}" alt="Play" width="28" height="28" />
                                    </label>
                                </div>
                            </div>

                            {{-- 3. PUSH THUMB VIDEO VÀO STACK SAU CÙNG (nằm cuối thanh thumbnail) --}}
                            @push('thumbItems')
                                <div>
                                    <label for="toggle-yt-popup-{{ $product['id'] }}" 
                                        class="glideThumbItem rounded-5 border border-D9D9D9 p-lg-5 p-3 rounded-5 thumb-cover product-image-sub position-relative d-block cursor-pointer m-0 @if($thumbIndex === 0) glide__slide--active @endif"
                                        style="cursor: pointer;"
                                        data-thumb-index="{{ $thumbIndex }}">
                                        <img src="{{ $ytThumbUrl }}"
                                            width="96" height="68"
                                            loading="lazy"
                                            alt="{{ $product['name'] ?? 'Video sản phẩm' }}"
                                            class="ratio-1 object-fit-contain rounded-5 w-100">
                                        <span class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <img src="{{ asset('/images/play.png') }}" alt="Play" width="16" height="16" />
                                        </span>
                                    </label>
                                </div>
                            @endpush
                            @php $thumbIndex++; @endphp
                        @endif

                    </div>
                </div>
                <div data-glide-el="controls">
                    <span data-glide-dir="<"
                        class="cursor-pointer btn-prev position-absolute top-50 translate-middle-y start-0 ms-1 "
                        style="left: 15px">
                        <i class="icon-left fs-8 fs-lg-30 p-2 btn-next-slider rounded-circle p-5 text-fff-hover"></i>
                    </span>
                    <span data-glide-dir=">"
                        class="cursor-pointer btn-next position-absolute top-50 translate-middle-y end-0 me-1 "
                        style="right: 15px">
                        <i class="icon-right fs-8 fs-lg-30 p-2 btn-next-slider rounded-circle p-5 text-fff-hover"></i>
                    </span>
                </div>
            </div>

            {{-- Thanh Slider Thumbnail bên dưới (Video sẽ nằm ở vị trí cuối cùng) --}}
            <div class="glideThumb glide" data-perview="4" data-autoplay="false">
                <div class="overflow-hidden rounded" data-glide-el="track">
                    <div class="row row-cols-4 g-2 flex-nowrap">
                        @stack('thumbItems')
                    </div>
                </div>
            </div>

            {{-- Phần Popup Video YouTube --}}
            @if (!empty($ytId) && $showFull)
                <input type="checkbox" id="toggle-yt-popup-{{ $product['id'] }}" class="d-none yt-checkbox-toggle">
                <div class="yt-video-modal-overlay" id="ytOverlay-{{ $product['id'] }}">
                    <label for="toggle-yt-popup-{{ $product['id'] }}" class="yt-overlay-close-bg"></label>
                    <div class="yt-modal-content-box">
                        <label for="toggle-yt-popup-{{ $product['id'] }}" class="yt-popup-close-btn">&times;</label>
                        <div class="ratio ratio-16x9 w-100">
                            <iframe class="yt-iframe" data-src="https://www.youtube.com/embed/{{ $ytId }}?autoplay=1" src="" title="{{ $product['name'] }}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>

                {{-- Script đồng bộ vị trí index giữa 2 slider --}}
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const glideBig = document.querySelector('.glideBig');
                        const glideThumb = document.querySelector('.glideThumb');
                        if (!glideBig || !glideThumb) return;

                        const bigSlider = new Glide(glideBig).mount();
                        const thumbSlider = new Glide(glideThumb).mount();

                        bigSlider.on('run.after', function() {
                            const activeIndex = bigSlider.index;
                            thumbSlider.go('=' + activeIndex);
                            
                            document.querySelectorAll('.glideThumb .glideThumbItem').forEach(item => {
                                item.classList.remove('glide__slide--active');
                            });
                            const activeThumb = document.querySelector(`.glideThumb .glideThumbItem[data-thumb-index="${activeIndex}"]`);
                            if (activeThumb) {
                                activeThumb.classList.add('glide__slide--active');
                            }
                        });
                    });
                </script>
                @endpush
            @endif
        </div>
    @endif
</div>
            <div class="col-lg-6">
                <div class="product-detail-name">
                    <h1 class="fs-20 fs-lg-22 fw-6 my-0">{{ $product['name'] }}</h1>
                </div>

                @php
                    // Kiểm tra xem sản phẩm này có thuộc danh mục 'phu-kien-may-hut-bui-cong-nghiep' hay không
                    $isAccessory = \App\Models\Category::whereHas('products', function($q) use ($product) {
                        $q->where('m_products.id', $product['id']);
                    })->where('slug', 'phu-kien-may-hut-bui-cong-nghiep')->exists();

                    // Thương hiệu chỉ hiển thị khi brand tồn tại VÀ đang bật (is_active = 1)
                    $activeBrandName = null;
                    if (!empty($product['brand_id']) && $product['brand_id'] > 0) {
                        $brandObj = \App\Models\Brand::where('id', $product['brand_id'])->where('is_active', 1)->first();
                        if ($brandObj) {
                            $activeBrandName = $brandObj->name;
                        }
                    } elseif (!empty($brand['name'])) {
                        $checkActive = \App\Models\Brand::where('name', $brand['name'])->where('is_active', 1)->first();
                        if ($checkActive) {
                            $activeBrandName = $brand['name'];
                        }
                    }

                    // 1. Lấy danh sách tất cả các ID của danh mục cha và các cấp con cháu của nó
                    $parentSlugs = ['phu-kien-may-hut-bui-cong-nghiep','phu-kien-thap-giai-nhiet', 'phu-kien-may-cha-san', 'phu-kien-may-rua-xe', 'phu-kien-xe-quet-rac'];
                    $parentIds = \App\Models\Category::whereIn('slug', $parentSlugs)->pluck('id')->toArray();
                    $allCategoryIds = $parentIds;
                    if (!empty($parentIds)) {
                        $currentParentIds = $parentIds;
                        while (!empty($currentParentIds)) {
                            $foundChildren = \App\Models\Category::whereIn('parent_id', $currentParentIds)->pluck('id')->toArray();
                            if (empty($foundChildren)) break;
                            $allCategoryIds = array_merge($allCategoryIds, $foundChildren);
                            $currentParentIds = $foundChildren;
                        }
                    }
                    // 2. Kiểm tra xem sản phẩm có thuộc bất kỳ ID nào trong cây danh mục đó không
                    $isAccessoryBH = \App\Models\Category::whereHas('products', function($q) use ($product) {
                        $q->where('m_products.id', $product['id']);
                    })->whereIn('id', $allCategoryIds)->exists();

                    $stockLabel = ($product['quantity'] ?? 1) > 0 ? 'Còn hàng' : 'Hết hàng';
                @endphp

                {{-- Dòng phụ: đánh giá - thương hiệu - bảo hành - so sánh (popup xem nhanh dùng chung như trang chi tiết) --}}
                <div class="pd-meta">
                    <span class="pd-rate">
                        <i class="icon-star-full"></i>
                        <strong>{{ number_format($reviewAvg ?? 0, 1, ',', '.') }}/5</strong>
                        <em>({{ $reviewTotal ?? 0 }} đánh giá)</em>
                    </span>
                    @if(!$isAccessory && !empty($activeBrandName))
                        <span class="pd-brand">Thương hiệu: <strong>{{ $activeBrandName }}</strong></span>
                    @endif
                    @if(!$isAccessoryBH)
                        <span class="pd-warranty">Bảo hành: <strong>{{ !empty($product['warranty']) ? $product['warranty'] . ' tháng' : '12 tháng' }}</strong></span>
                    @endif
                    @if ($showFull)
                        <div class="text-D80000 d-flex align-items-center z-2 cursor-pointer jsShowModalCompareProduct"
                            data-id="{{ $product['id'] }}">
                            <i class="icon-add-outline"></i>
                            <span class="ms-1 fs-14 fw-5">So sánh</span>
                        </div>
                    @endif
                </div>

                    <div class="price-section">
                        @if (!empty($product['sale_price']))
                            <!-- Trường hợp có giá giảm -->
                            <span class="price-new">{{ number_format($product['sale_price'], 0, ',', '.') }}đ</span>
                            <span class="price-old">{{ number_format($product['price'], 0, ',', '.') }}đ</span>
                            @if (!empty($product['discount_percent']))
                                <span class="discount">-{{ $product['discount_percent'] }}%</span>
                            @endif
                            <span class="pd-stock {{ ($product['quantity'] ?? 1) > 0 ? 'is-in' : 'is-out' }}">{{ $stockLabel }}</span>
                            <div class="text-secondary">(Giá chưa bao gồm VAT)</div>
                        @else
                            <!-- Trường hợp giá thường -->
                            <span class="price-new">
                                {{ $product['price'] ? number_format($product['price'], 0, ',', '.') . 'đ' : 'Liên hệ' }}
                            </span>
                            <span class="pd-stock {{ ($product['quantity'] ?? 1) > 0 ? 'is-in' : 'is-out' }}">{{ $stockLabel }}</span>
                            @if (!empty($product['price']))
                                <div class="text-secondary">(Giá chưa bao gồm VAT)</div>
                            @endif
                        @endif
                    </div>
                    @if (!empty($product['promotion']))
                        <div class="promotion-box">
                            <strong><svg class="pm-gift" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 11h18v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9ZM2 7h20v4H2V7Zm9 0v14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M11 7H8.5a2.5 2.5 0 0 1 0-5C11 2 11 7 11 7Zm2 0h2.5a2.5 2.5 0 0 0 0-5C13 2 13 7 13 7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>Khuyến mãi</strong>
                            <div class="promotion-text">
                                {!! str_replace('<ul', '<ul class="m-0 p-0 content-promotion fs-15"', $product['promotion']) !!}
                            </div>
                        </div>
                    @else
                        
                    @endif
                    @if(!empty($categoryCoupons) && is_array($categoryCoupons))
                        <div class="coupon-box">
                            <div class="coupon-box-title">Mã khuyến mãi</div>
                            <div class="coupon-box-wrapper">
                                @foreach($categoryCoupons as $coupon)
                                    @php
                                        $code = $coupon['code'] ?? '';
                                        $discount = $coupon['discount'] ?? '';
                                        $minSpend = $coupon['min_spend'] ?? 0;
                                        $couponDesc = $coupon['desc'] ?? '';
                                        
                                        $minSpendFormatted = is_numeric($minSpend) ? number_format($minSpend, 0, ',', '.') . 'đ' : $minSpend;
                                    @endphp

                                    @if(!empty($code))
                                        <div class="coupon-item">
                                            <span class="coupon-ticket">
                                                <img src="{{ asset('/images/gif.png') }}" width="32" height="32" alt="Biểu tượng quà tặng - mã khuyến mãi" loading="lazy">
                                            </span>
                                            <span class="coupon-body">
                                                <p>
                                                    <strong class="coupon-title">{{ !empty($couponDesc) ? $couponDesc : ('Giảm ' . $discount) }}</strong>
                                                    @if(empty($couponDesc))
                                                        cho đơn hàng từ <strong>{{ $minSpendFormatted }}</strong>
                                                    @endif
                                                    <br>Mã <strong class="coupon-code">{{ $code }}</strong>
                                                </p>
                                                <button type="button" class="btn-copy-coupon" data-code="{{ $code }}">Lưu mã</button>
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                <div class="policy-box">
                    <div class="policy-box-title">Chính sách bán hàng</div>
                    <div class="policy-grid">
                        <div class="policy-item">
                            <img src="{{ asset('/images/icon1.svg') }}" width="24" height="24" alt="Cam kết hàng chính hãng 100%" loading="lazy">
                            <span>Cam kết hàng chính hãng 100%</span>
                        </div>
                        <div class="policy-item">
                            <img src="{{ asset('/images/icon4.svg') }}" width="24" height="24" alt="Bảo hành chính hãng, lắp đặt trên toàn quốc" loading="lazy">
                            <span>Bảo hành chính hãng, lắp đặt trên toàn quốc</span>
                        </div>
                        <div class="policy-item">
                            <img src="{{ asset('/images/icon2.svg') }}" width="24" height="24" alt="Miễn phí giao hàng nội thành với đơn hàng từ 2 triệu đồng" loading="lazy">
                            <span>Miễn phí giao hàng nội thành với đơn hàng từ 2 triệu đồng</span>
                        </div>
                        <div class="policy-item">
                            <img src="{{ asset('/images/icon3.svg') }}" width="24" height="24" alt="Giá tốt nhất, đặc biệt khi mua từ 3 sản phẩm" loading="lazy">
                            <span>Giá tốt nhất, đặc biệt khi mua từ 3 sản phẩm</span>
                        </div>
                    </div>
                </div>  
                @if ($product['instock'] && $showFull)
                    @php $qtyId = 'quantityOrder'; @endphp
                    <div class="qty-line d-flex align-items-center">
                        <span class="me-2">Số lượng:</span>

                        <label
                            class="input-number d-flex align-items-center border rounded-5 border-D9D9D9 overflow-hidden">
                            <span class="btn-change cursor-pointer text-center" data-step="-1"
                                style="padding-top: 5px">
                                <i class="icon-minus"></i></span>
                            <input type="number" class="border-0 text-center fs-16 fw-5" id="{{ $qtyId }}"
                                value="1" min="1" name="quantity"
                                style="width: 30px; outline: none;">
                            <span class="btn-change cursor-pointer px-3 text-center fs-20 fw-7" data-step="1"
                                style="padding-top: 2px;">+</span>
                        </label>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-buy btnBuy" data-id="{{ $product['id'] }}"
                            onclick="this.setAttribute('data-quantity', document.getElementById('{{ $qtyId }}').value)">
                            <svg width="25" height="25" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2 3h3l2.6 12.3a2 2 0 0 0 2 1.7h7.9a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="10" cy="21" r="1.6" fill="currentColor"/><circle cx="18" cy="21" r="1.6" fill="currentColor"/>
                            </svg>
                            <span class="btn-label">MUA NGAY</span>
                        </button>

                        <button type="button" class="btn-quote" data-quote-phone="{{ $phoneHn }}">
                            <svg width="25" height="25" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 2h8l5 5v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M13 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="btn-label">YÊU CẦU BÁO GIÁ<br><em>(Khi mua từ 3 sản phẩm)</em></span>
                        </button>
                    </div>

                    {{-- PC (pointer: fine) -> mở dialog form; điện thoại/tablet -> product.js gọi tel: --}}
                    <dialog class="quote-dialog" id="quoteDialog" aria-labelledby="quoteDialogTitle">
                        <form method="post" action="{{ route('frontend.quote.store') }}"
                            class="quote-form" data-onsuccess="onQuoteSent" data-onerror="onQuoteError">
                            @csrf
                            <div class="quote-dialog-head">
                                <h3 id="quoteDialogTitle">Yêu cầu báo giá</h3>
                                <button type="button" class="quote-dialog-close" aria-label="Đóng">&times;</button>
                            </div>
                            <p class="quote-dialog-product">{{ $product['name'] ?? '' }}</p>

                            <label class="quote-field">
                                <span>Họ và tên <em>*</em></span>
                                <input type="text" name="name" required maxlength="255" autocomplete="name"
                                    placeholder="Nhập họ và tên">
                            </label>
                            <label class="quote-field">
                                <span>Số điện thoại <em>*</em></span>
                                <input type="tel" name="phone" required maxlength="20" autocomplete="tel"
                                    inputmode="tel" placeholder="Nhập số điện thoại">
                            </label>
                            <label class="quote-field">
                                <span>Email (không bắt buộc)</span>
                                <input type="email" name="email" maxlength="255" autocomplete="email"
                                    placeholder="Nhập email">
                            </label>
                            <label class="quote-field">
                                <span>Ghi chú</span>
                                <textarea name="note" rows="3" maxlength="2000"
                                    placeholder="Ví dụ: cần 5 máy, giao tại Bắc Ninh, cần báo giá kèm vận chuyển..."></textarea>
                            </label>

                            <input type="hidden" name="product_id" value="{{ $product['id'] ?? '' }}">
                            <input type="hidden" name="quantity" value="1">

                            <button type="submit" class="quote-submit">Gửi yêu cầu báo giá</button>
                            <p class="quote-dialog-hotline">Hoặc gọi trực tiếp
                                <a href="tel:{{ $phoneHn }}">{{ $phoneHn }}</a>
                            </p>
                        </form>
                    </dialog>
                @elseif (!$showFull)
                    <div class="action-buttons">
                        <a class="btn-quote" href="{{ $product['url'] }}">
                            <span class="btn-label">XEM CHI TIẾT</span>
                        </a>
                    </div>
                @endif      
            </div>
        </div>
    </div>
</div>


@pushonce('lazyContent')
    <dialog id="modalDetailProduct" class="col-12 border-0 shadow-none outline-0 p-3 g-3  bg-fff">
        <div class="gx-2 gx-xl-4 showContent">

        </div>
        <button type="button" class="qv-close" data-target-modal data-close="modalDetailProduct" aria-label="Đóng">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </dialog>
    <dialog id="modalCompareProduct"
        class="col-12 border-0 shadow-none outline-0 g-3 position-fixed bottom-0 mb-lg-5 z-3 bg-fff" style="padding: 10px 15px">
        <div class="container gx-2 gx-xl-4 shadow  showContent pe-25">

        </div>
        <span class="p-5 position-absolute cursor-pointer" data-target-modal
            data-close="modalCompareProduct" style="top: -28px; right: -26px; color: #d50118">
            <i class="icon-close fs-30" style="box-shadow: 0 0px 3px 0 rgb(60 64 67 / 58%), 0 0px 7px 0px rgba(60, 64, 67, 0.55);
    border-radius: 100px;"></i>
        </span>
    </dialog>
    <dialog id="modalCompareSelectProduct" class="col-12 col-lg-10 border-0 shadow-none outline-0 p-1 p-lg-3 g-3 bg-fff">
        <div class="container gx-2 gx-xl-4">
            <span class="p-2 position-absolute top-0 end-0 cursor-pointer fs-20" data-target-modal
                data-close="modalCompareSelectProduct">
                <i class="icon-close"></i>
            </span>
            <div class="text-center fw-6 p-3 border-bottom">Thêm sản phẩm so sánh</div>
            <div class="p-1 p-lg-3">
                <div class="p-2">Tìm sản phẩm</div>
                <div class="border rounded-4 p-2 mb-3">
                    <i class="icon-search"></i>
                    <input type="text" name="keyword" placeholder="Nhập tên sản phẩm" class="border-0">
                </div>
                <div class="showContent row g-2" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;"></div>
            </div>
        </div>
    </dialog>
@endpushonce
@pushonce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Common.loadJs('_Library.min.js').then(() => {
                Library.init();
            });
            Common.loadJs('CustomGlide.min.js').then(() => {
                CustomGlide.initAll()
            })
        });
    </script>
@endpushonce

@push('scripts-header')
@endpush

@push('scripts') 
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Common.loadJs('_modalDetailProduct.js');
            Common.loadJs('_wishlist.js');
        });
    </script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.yt-checkbox-toggle').forEach(checkbox => {
            let productId = checkbox.id.replace('toggle-yt-popup-', '');
            let overlay = document.getElementById('ytOverlay-' + productId);
            
            if (!overlay) return;
            
            let iframe = overlay.querySelector('.yt-iframe');
            let videoSrc = iframe ? iframe.getAttribute('data-src') : '';

            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    overlay.classList.add("is-active");
                    overlay.style.display = "flex"; 
                    document.body.style.overflow = "hidden"; 

                    if (iframe && videoSrc) {
                        iframe.src = videoSrc;
                    }
                } else {
                    closePopup();
                }
            });

            function closePopup() {
                checkbox.checked = false;
                overlay.classList.remove("is-active");
                overlay.style.display = "none";
                document.body.style.overflow = ""; 

                if (iframe) {
                    iframe.src = "";
                }
            }

            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && overlay.classList.contains("is-active")) {
                    closePopup();
                }
            });
        });
    });
</script>
@endpush