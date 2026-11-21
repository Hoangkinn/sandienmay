@php
    use App\Enums\ProductCategoryFilterPrice;
@endphp
@extends('web.layout')

@section('body-class') cate-v2 @endsection

@once
    @php
        $customSchema = null;
        if (isset($category)) {
            if (is_array($category)) {
                $customSchema = $category['schema'] ?? null;
            } elseif (is_object($category)) {
                $customSchema = $category->schema ?? null;
            }
        }
        $customSchema = is_string($customSchema) ? trim($customSchema) : null;
    @endphp

    {{-- NẾU ADMIN CÓ NHẬP: Ẩn 3 cái cứng đi bằng cách định nghĩa section rỗng --}}
    @if(!empty($customSchema))
        @section('default-schema')
            {{-- Để trống để ẩn 3 schema cứng --}}
        @endsection
    @endif

    @push('schema')
        @if(!empty($customSchema))
            {{-- Hiển thị schema của admin --}}
            @if(str_starts_with($customSchema, '<script'))
                {!! $customSchema !!}
            @else
                <script type="application/ld+json">
                {!! $customSchema !!}
                </script>
            @endif
        @else
            {{-- Nếu trống thì hiển thị Breadcrumb tự động (3 cái cứng ở layout vẫn giữ nguyên bình thường) --}}
            @php
                $breadcrumbItems = [];
                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => url('/'),
                ];

                $pos = 2;
                if (!empty($cateBr) && is_iterable($cateBr)) {
                    foreach ($cateBr as $cat) {
                        $breadcrumbItems[] = [
                            '@type' => 'ListItem',
                            'position' => $pos++,
                            'name' => $cat['name'],
                            'item' => $cat['url'],
                        ];
                    }
                }

                $breadcrumbSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $breadcrumbItems,
                ];
            @endphp

            <script type="application/ld+json">
                {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
    @endpush
@endonce

@push('page-css')
    @php
        $cateV2Path = 'css-new/dist/category-v2.min.css';
        $cateV2Ver = file_exists(public_path($cateV2Path)) ? filemtime(public_path($cateV2Path)) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset($cateV2Path . '?v=' . $cateV2Ver) }}">
@endpush

@section('content')
    @php
        $attributeCats = collect($relatedCat ?? [])->where('display_type', '!=', 'brand')->values();
        // Lưới danh mục chỉ sắp xếp theo giá (cao -> thấp trước), 2 lựa chọn như trang mẫu
        $sortMap = collect($priceSorts ?? [])->keyBy(fn ($s) => $s->value);
        $sortOptions = array_values(array_filter([
            $sortMap->get('price-desc'),
            $sortMap->get('price-asc'),
        ]));
        $sortLabels = [
            'price-desc' => 'Giá Cao - Thấp',
            'price-asc' => 'Giá Thấp - Cao',
        ];
    @endphp

    <div class="container">
        <nav class="breadcrumb-bar" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Trang chủ</a></li>
                @foreach ($cateBr as $i => $crumb)
                    <li>
                        @if ($i === count($cateBr) - 1)
                            <span aria-current="page">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        @if (!empty($category['banner']))
            <section class="cate-banner">
                <img src="{{ url($category['banner']) }}" alt="{{ $category['name'] }}" class="responsive-img">
            </section>
        @endif

        <div class="cate-head">
            <h1>{{ $category['name'] }}</h1>
        </div>

        <div class="filter-backdrop" id="filterBackdrop"></div>

        <form method="get" id="formFilter" action="{{ $category['url'] }}">
            <div class="filter-card">
                @if ($brandCats->isNotEmpty())
                    <div class="filter-group">
                        <span class="filter-group-label">Thương hiệu nổi bật</span>
                        <div class="filter-brand-list">
                            @foreach ($brandCats as $brandItem)
                                <label class="filter-brand">
                                    <input type="radio" name="brand" value="{{ $brandItem->id }}"
                                        @checked($brand_id && $brandItem->id == $brand_id)>
                                    @if (!empty($brandItem->image))
                                        <img src="{{ url('/storage/' . $brandItem->image) }}" alt="{{ $brandItem->name }}"
                                            width="96" height="26" loading="lazy">
                                    @else
                                        <span>{{ $brandItem->name }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($attributeCats->isNotEmpty())
                    <div class="filter-group">
                        <span class="filter-group-label">Chọn theo nhu cầu</span>
                        <div class="filter-attr-list">
                            @foreach ($attributeCats as $i)
                                <div class="filter-attr custom-clickable-item" data-url="{{ $i['url'] ?? '' }}">
                                    <img src="{{ $i['image'] }}" alt="{{ $i['name'] }}" width="52" height="52" loading="lazy">
                                    <span>{{ $i['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    // Danh mục cấp 1 có cấu hình mức giá riêng -> dùng nó; không có thì rơi về
                    // 7 khoảng enum cũ. value "{min}-{max}" khớp đúng format chip ngoài menu.
                    $bandOptions = collect($priceBands ?? [])->map(function ($b) {
                        return [
                            'label' => $b['label'],
                            'value' => ($b['min'] ?? 0) . '-' . ($b['max'] ?? ''),
                        ];
                    })->all();
                @endphp

                <div class="filter-group">
                    <span class="filter-group-label">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                        Khoảng giá
                    </span>
                    <div class="filter-price-list">
                        @if (!empty($bandOptions))
                            @foreach ($bandOptions as $option)
                                <label class="filter-chip">
                                    <input type="radio" name="price_range" value="{{ $option['value'] }}"
                                        @checked($option['value'] === $priceRange)>
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @endforeach
                        @else
                            @foreach (ProductCategoryFilterPrice::cases() as $i)
                                <label class="filter-chip">
                                    <input type="radio" name="price" value="{{ $i->value }}" @checked($filterPriceEnum == $i)>
                                    <span>{{ $i->getTitle() }}</span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>

            <div class="sort-bar">
                <h2 class="found">Tìm thấy <b>{{ number_format($total, 0, ',', '.') }}</b> sản phẩm</h2>
                <div class="sort-pills">
                    <span class="sort-label">Sắp xếp theo <strong>(giá):</strong></span>
                    @foreach ($sortOptions as $i)
                        @php $sortName = $sortLabels[$i->value] ?? $i->getTitle(); @endphp
                        <label class="sort-pill">
                            {{-- aria-label: tren mobile <span> bị display:none nên radio khong con ten --}}
                            <input type="radio" name="sort" value="{{ $i->value }}" aria-label="{{ $sortName }}" @checked($sortEnum == $i)>
                            @if ($i->value === 'price-desc')
                                <svg class="sort-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M6 20l-3.5-3.5M6 20l3.5-3.5"/><path d="M13 5h8M13 11h6M13 17h4"/></svg>
                            @else
                                <svg class="sort-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20V4M6 4L2.5 7.5M6 4l3.5 3.5"/><path d="M13 7h4M13 13h6M13 19h8"/></svg>
                            @endif
                            <span>{{ $sortName }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="cate-products d-grid template5 gap15" id="listProduct">
                @if (count($products) > 0)
                    <x-fe.products :products="$products" :viewSoldQuantity="true" />
                @else
                    <p class="cate-empty">Danh mục này chưa có sản phẩm nào.</p>
                @endif
            </div>

            <div id="productPagination">
                @if ($nextPageUrl)
                    <div class="load-more-wrap">
                        <a href="{{ $nextPageUrl }}" class="btn-load-more">Xem thêm {{ number_format($loadMoreCount, 0, ',', '.') }} sản phẩm
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </form>

        @if (!empty($category['content']))
            <div class="row g-2 mb-5 mt-3">
                <div class="content content-maxH order-lg-1 bg-fff pt-16 px-15 pb-50 rounded-10">
                    @include('components/fe/toc', ['toc' => $category['toc'] ?? []])
                    {!! $category['content'] !!}
                </div>
            </div>
        @endif

        @if ($news->isNotEmpty())
            <section class="cate-news">
                <h2 class="cate-news-title">Tin tức liên quan</h2>
                <div class="cate-news-list">
                    @foreach ($news as $item)
                        <a href="{{ $item['url'] }}" class="cate-news-item">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                            <h3>{{ $item['name'] }}</h3>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoryUrl = new URL(@json($category['url']), window.location.href);
            const listProduct = document.getElementById('listProduct');
            const filterInputSelector = '#formFilter input[type="radio"]';
            // Bộ lọc chỉ sống trong bộ nhớ: address bar luôn là /slug, không có ?brand ?price ?sort ?page
            const filterState = {
                brand: @json($brand_id),
                price: @json($price),
                price_range: @json($priceRange !== '' ? $priceRange : null),
                attr: @json($attrOption),
                sort: @json($sortEnum?->value),
            };
            let loadedPage = 1;
            let loading = false;

            function buildRequestUrl(page = 1) {
                const url = new URL(categoryUrl.href);
                url.search = '';
                Object.entries(filterState).forEach(([key, value]) => {
                    if (value) url.searchParams.set(key, value);
                });
                if (page > 1) url.searchParams.set('page', page);
                return url;
            }

            // Bấm lại vào lựa chọn đang chọn = bỏ lọc
            const radioCheckedBeforeClick = new WeakMap();
            document.body.addEventListener('pointerdown', function (e) {
                const input = e.target.closest(filterInputSelector) || e.target.closest('label')?.querySelector(filterInputSelector);
                if (input) radioCheckedBeforeClick.set(input, input.checked);
            }, true);

            document.body.addEventListener('click', function (e) {
                const filterInput = e.target.closest(filterInputSelector) || e.target.closest('label')?.querySelector(filterInputSelector);
                if (filterInput && radioCheckedBeforeClick.get(filterInput)) {
                    e.preventDefault();
                    setTimeout(() => {
                        filterInput.checked = false;
                        filterState[filterInput.name] = null;
                        fetchProducts(buildRequestUrl().toString());
                    }, 0);
                    return;
                }

                // Thẻ "Chọn theo nhu cầu" -> sang thẳng danh mục con
                const featureItem = e.target.closest('.custom-clickable-item');
                if (featureItem) {
                    const targetUrl = featureItem.getAttribute('data-url');
                    if (targetUrl && targetUrl !== '#') window.location.href = targetUrl;
                    return;
                }

                // "Xem thêm N sản phẩm" -> nối tiếp vào lưới hiện tại
                const loadMoreLink = e.target.closest('#productPagination a');
                if (loadMoreLink) {
                    e.preventDefault();
                    const href = loadMoreLink.getAttribute('href');
                    if (!href) return;
                    const page = parseInt(new URL(href, window.location.href).searchParams.get('page'), 10) || (loadedPage + 1);
                    fetchProducts(buildRequestUrl(page).toString(), { append: true });
                }
            });

            document.body.addEventListener('change', function (e) {
                const filterInput = e.target.closest(filterInputSelector);
                if (!filterInput) return;
                filterState[filterInput.name] = filterInput.value;
                fetchProducts(buildRequestUrl().toString());
            });

            document.getElementById('formFilter')?.addEventListener('submit', e => e.preventDefault());

            function fetchProducts(url, options = {}) {
                if (loading) return;
                loading = true;
                const requestUrl = new URL(url, window.location.href);
                if (listProduct) {
                    listProduct.style.opacity = '0.35';
                    listProduct.style.pointerEvents = 'none';
                }

                fetch(requestUrl.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const newProducts = doc.getElementById('listProduct');
                        if (newProducts && listProduct) {
                            if (options.append) listProduct.append(...newProducts.children);
                            else listProduct.innerHTML = newProducts.innerHTML;
                        }

                        const newCount = doc.querySelector('.sort-bar .found');
                        const currentCount = document.querySelector('.sort-bar .found');
                        if (newCount && currentCount) currentCount.innerHTML = newCount.innerHTML;

                        const newPagination = doc.getElementById('productPagination');
                        const currentPagination = document.getElementById('productPagination');
                        if (newPagination && currentPagination) currentPagination.innerHTML = newPagination.innerHTML;

                        loadedPage = parseInt(requestUrl.searchParams.get('page'), 10) || 1;

                        if (typeof Common !== 'undefined' && typeof Common.lazyImage === 'function') Common.lazyImage();
                        if (typeof ModalDetailProduct !== 'undefined') ModalDetailProduct.addEventClick();
                    })
                    .catch(error => console.error('Lỗi tải dữ liệu sản phẩm:', error))
                    .finally(() => {
                        loading = false;
                        if (listProduct) {
                            listProduct.style.opacity = '1';
                            listProduct.style.pointerEvents = 'auto';
                        }
                    });
            }
        });
    </script>
@endpush

