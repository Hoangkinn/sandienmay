@php use App\Enums\ProductCategoryFilterPrice;use App\Helpers\BreadcrumbHelper; @endphp
@extends('web.layout')

@push('page-css')
    @php
        $filterV2Path = 'css-new/dist/category-v2.min.css';
        $filterV2Ver = file_exists(public_path($filterV2Path)) ? filemtime(public_path($filterV2Path)) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset($filterV2Path . '?v=' . $filterV2Ver) }}">
@endpush

@section('content')
    @php
        // Bộ lọc trang tìm kiếm chỉ sắp xếp theo giá (cao -> thấp trước), 2 lựa chọn như trang danh mục
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

    {!! BreadcrumbHelper::render() !!}
    <style>
        .search-result {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 5px auto 0;
        }
        .search-result span {
            padding: 6px 14px;
            border: 1px solid #888;
            border-radius: 5px;    
            cursor: pointer;
            width: max-content;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .search-result span:hover,
        .search-result span.active {
            background-color: #26348b;
            border: 1px solid #26348b;
            color: #fff;
        }
        /* Ẩn/Hiện nội dung tab */
        .tab-content-pane {
            display: none;
        }
        .tab-content-pane.active {
            display: block;
        }
    </style>

    <div class="container container px-lg-0 px-2 filter-v2">
        <div class="d-flex flex-column justify-content-center text-center my-4" style="padding: 20px 0">
            <h1 class="fs-16 fs-lg-18 text-uppercase">Kết quả tìm kiếm cho từ khóa: "{{$keyword}}"</h1>
            <div class="search-result">
                <span class="fs-15 text-000 fw-4 tab-btn" data-target="products-section">
                    {{$total}} sản phẩm
                </span>
                <span class="fs-15 text-000 fw-4 tab-btn" data-target="posts-section">
                    {{ isset($posts) ? count($posts) : 0 }} bài viết
                </span>
            </div>
        </div>

        <!-- KHUNG LỌC SẢN PHẨM -->
        <form method="get" id="formFilter" action="{{ route('frontend.page.search') }}">
            <input type="hidden" name="s" value="{{ $keyword }}">

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

                <div class="filter-group">
                    <span class="filter-group-label">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                        Khoảng giá
                    </span>
                    <div class="filter-price-list">
                        @foreach (ProductCategoryFilterPrice::cases() as $i)
                            <label class="filter-chip">
                                <input type="radio" name="price" value="{{ $i->value }}" @checked($filterPriceEnum == $i)>
                                <span>{{ $i->getTitle() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sort-bar">
                <p class="found">Tìm thấy <b>{{ number_format($total, 0, ',', '.') }}</b> sản phẩm</p>
                <div class="sort-pills">
                    <span class="sort-label">Sắp xếp theo <strong>(giá):</strong></span>
                    @foreach ($sortOptions as $i)
                        <label class="sort-pill">
                            <input type="radio" name="sort" value="{{ $i->value }}" @checked($sortEnum == $i)>
                            @if ($i->value === 'price-desc')
                                <svg class="sort-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M6 20l-3.5-3.5M6 20l3.5-3.5"/><path d="M13 5h8M13 11h6M13 17h4"/></svg>
                            @else
                                <svg class="sort-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20V4M6 4L2.5 7.5M6 4l3.5 3.5"/><path d="M13 7h4M13 13h6M13 19h8"/></svg>
                            @endif
                            <span>{{ $sortLabels[$i->value] ?? $i->getTitle() }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </form>

        <!-- TAB 1: SẢN PHẨM -->
        <div id="products-section" class="tab-content-pane">
            <div class="col-lg-12 pb-10">
                <div class="d-grid template4 listProduct-search" id="listProduct" style="gap: 15px">
                    @if(!empty($products) && (is_countable($products) ? count($products) > 0 : true))
                        <x-fe.products :products="$products" :preload="true" />
                    @else
                        <p>Không có sản phẩm nào phù hợp với bộ lọc hiện tại.</p>
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
            </div>
        </div>

        <!-- TAB 2: BÀI VIẾT TIN TỨC -->
        <div id="posts-section" class="tab-content-pane">
            <div class="col-lg-12 pb-50 mt-3">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="fs-16 fs-lg-18 text-uppercase">Tin tức & Bài viết liên quan</h2>
                </div>

                @if(!empty($posts) && count($posts) > 0)
                    <div class="d-grid template4 listProduct-search">
                        @foreach($posts as $post)
                            <a href="{{ $post['slug'] }}" class="tech-card text-decoration-none d-block bg-fff rounded-10 h-100">
                                <div class="tech-thumb overflow-hidden rounded-8">
                                    <img src="{{ asset('storage/' . $post['image']) }}" alt="{{ $post['title'] }}" class="w-100 object-fit-cover" style="aspect-ratio: 4/3;">
                                </div>
                                <div class="tech-time">
                                    <p>{{ \Carbon\Carbon::parse($post['date'])->format('d') }}</p>
                                    <span>{{ \Carbon\Carbon::parse($post['date'])->format('m/Y') }}</span>
                                </div>
                                <div class="tech-text">
                                    <h3 class="fs-14 fw-6 text-dark mb-0 line-clamp-2">{{ $post['title'] }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-fff rounded-10 p-15">
                        <p class="mb-0 text-muted">Không tìm thấy bài viết tin tức nào phù hợp.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Common.loadJs('CustomGlide.min.js').then(() => {
            CustomGlide.initAll()
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const totalProducts = {{ $total ?? 0 }};
        const totalPosts = {{ isset($posts) ? count($posts) : 0 }};
        
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-content-pane');
        const formFilter = document.getElementById('formFilter');

        function switchTab(targetId) {
            tabBtns.forEach(btn => {
                if (btn.getAttribute('data-target') === targetId) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            tabPanes.forEach(pane => {
                if (pane.id === targetId) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });

            // Ẩn khung sắp xếp nếu đang ở tab tin tức, hiện nếu ở tab sản phẩm
            if (formFilter) {
                if (targetId === 'products-section') {
                    formFilter.style.display = 'block';
                } else {
                    formFilter.style.display = 'none';
                }
            }
        }

        // Tự động xác định tab mặc định dựa trên số lượng sản phẩm/bài viết
        let defaultTab = 'products-section';
        if (totalProducts <= 0 && totalPosts > 0) {
            defaultTab = 'posts-section';
        }
        switchTab(defaultTab);

        // Sự kiện click chuyển tab
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const target = this.getAttribute('data-target');
                switchTab(target);
            });
        });
    });
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchUrl = new URL(@json(route('frontend.page.search')), window.location.href);
            const keyword = @json($keyword);
            const listProduct = document.getElementById('listProduct');
            const filterInputSelector = '#formFilter input[type="radio"]';
            // Bộ lọc chỉ sống trong bộ nhớ: address bar luôn là /tim-kiem?s=tu-khoa
            const filterState = {
                brand: @json($brand_id),
                price: @json($price),
                sort: @json($sortEnum?->value),
            };
            let loadedPage = 1;
            let loading = false;

            function buildRequestUrl(page = 1) {
                const url = new URL(searchUrl.href);
                url.search = '';
                url.searchParams.set('s', keyword);
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
                        if (newCount && currentCount) {
                            currentCount.innerHTML = newCount.innerHTML;
                            // Số trên nhãn tab sản phẩm cũng phải theo bộ lọc đang áp dụng
                            const tabProducts = document.querySelector('.tab-btn[data-target="products-section"]');
                            const found = newCount.querySelector('b');
                            if (tabProducts && found) tabProducts.textContent = found.textContent + ' sản phẩm';
                        }

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
