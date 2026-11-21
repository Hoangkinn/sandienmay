@php
    // Nhãn HOT/NEW chỉ áp dụng cho danh mục từ cấp 2 (badge = null thì không hiển thị)
    $renderBadge = function ($cate) {
        $badge = $cate['badge'] ?? null;
        if (!in_array($badge, ['hot', 'new'], true)) {
            return '';
        }
        $icon = $badge === 'hot' ? '/images/hot.svg' : '/images/new.png';
        $altBadge = $badge === 'hot' ? 'Danh mục bán chạy' : 'Danh mục sản phẩm mới';
        return '<span class="cate-badge cate-badge--' . $badge . '">'
            . '<img src="' . $icon . '" alt="' . $altBadge . '">' . strtoupper($badge)
            . '</span>';
    };

    // Drawer mobile hoa hết nhãn nhóm; strtoupper() của PHP bỏ dấu tiếng Việt nên dùng Str::upper
    $groupTitle = fn ($key) => \Illuminate\Support\Str::upper($groupLabels[$key] ?? $key);
@endphp

@if($mode === 'mega' || $mode === 'sidebar')
    {{-- Mega menu desktop / sidebar trang chủ: rail danh mục cấp 1 + các cột nhóm --}}
    <div class="mega-inner">
        <nav class="mega-rail" aria-label="Danh mục sản phẩm">
            <ul class="mega-rail-list">
                @foreach($categories as $cate)
                    @php $subcols = $cate['subcols'] ?? []; @endphp
                    <li class="mega-rail-item{{ $subcols ? '' : ' mega-rail-item--leaf' }}"
                        data-mega-index="{{ $loop->index }}">
                        <a class="mega-rail-link" href="{{ $cate['url'] }}" title="{{ $cate['name'] }}">
                            @if(!empty($cate['image']))
                                <img src="{{ $cate['image'] }}" alt="icon danh mục {{ $cate['name'] }}" width="28" height="28" loading="lazy" class="mega-rail-icon">
                            @endif
                            <span class="mega-rail-name">{{ $cate['name'] }}</span>
                            @if(!empty($subcols))
                                <i class="mega-rail-arrow" aria-hidden="true"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mega-body">
            @foreach($categories as $cate)
                @php $subcols = $cate['subcols'] ?? []; @endphp
                {{-- Danh mục cấp 1 không có con: sidebar vẫn mở pane rỗng (nền trắng, không giữ
                     submenu của danh mục trước đó); panel header thì JS bỏ qua, giữ nguyên danh sách --}}
                <div class="mega-pane" data-mega-pane="{{ $loop->index }}">
                    @if(!empty($subcols))
                        <div class="mega-cols">
                            @foreach($subcols as $col)
                                <section class="mega-col">
                                    <a class="mega-col-title" href="{{ $col['url'] }}" title="{{ $col['name'] }}">
                                        {{ $col['name'] }}{!! $renderBadge($col) !!}
                                    </a>
                                    <a class="mega-col-all" href="{{ $col['url'] }}">
                                        Xem tất cả <span aria-hidden="true">›</span>
                                    </a>
                                    @if(!empty($col['children']))
                                        <ul class="mega-col-list">
                                            @foreach($col['children'] as $child)
                                                <li>
                                                    <a class="mega-col-link" href="{{ $child['url'] }}" title="{{ $child['name'] }}">
                                                        {{ $child['name'] }}{!! $renderBadge($child) !!}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </section>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@else
    {{-- Drawer mobile: danh mục cấp 1 dạng accordion --}}
    <ul class="menu-list">
        @foreach($categories as $cate)
            @if(empty($cate['childrens']) || count($cate['childrens']) == 0)
                <li>
                    <a href="{{ $cate['url'] }}" title="{{ $cate['name'] }}" class="cate-Mb">
                        @if(!empty($cate['image']))
                            <img src="{{ $cate['image'] }}" alt="icon danh mục {{ $cate['name'] }}" width="26" height="26" class="icon-cate d-lg-none ratio-1x1 me-1">
                        @endif
                        {{ $cate['name'] }}
                    </a>
                </li>
            @else
                {{-- Kiểm tra xem thực tế có đứa con nào bật is_menu không, nếu không có thì coi như không có submenu --}}
                @php
                    $hasActiveChildren = false;
                    foreach($cate['childrens'] as $groupItems) {
                        foreach($groupItems as $child) {
                            if (!empty($child['is_menu'])) {
                                $hasActiveChildren = true;
                                break 2;
                            }
                        }
                    }
                @endphp

                @if(!$hasActiveChildren)
                    {{-- Nếu tất cả con đều tắt menu thì chỉ hiển thị dạng link thường thôi --}}
                    <li>
                        <a href="{{ $cate['url'] }}" title="{{ $cate['name'] }}" class="cate-Mb">
                            @if(!empty($cate['image']))
                                <img src="{{ $cate['image'] }}" alt="icon danh mục {{ $cate['name'] }}" width="26" height="26" class="icon-cate d-lg-none ratio-1x1 me-1">
                            @endif
                            {{ $cate['name'] }}
                        </a>
                    </li>
                @else
                    <li class="has-submenu-custom">
                        <div class="menu-item-row d-flex align-items-center justify-content-between">
                            <a href="{{ $cate['url'] }}" title="{{ $cate['name'] }}" class="flex-grow-1 cate-Mb">
                                @if(!empty($cate['image']))
                                    <img src="{{ $cate['image'] }}" alt="icon danh mục {{ $cate['name'] }}" width="26" height="26" class="icon-cate d-lg-none ratio-1x1 me-1">
                                @endif
                                {{ $cate['name'] }}
                            </a>
                            <span class="submenu-toggle-btn"></span>
                        </div>

                        {{-- Phần menu cấp 2 chia theo nhóm display_type --}}
                        <div class="sub-menu-custom bg-fff p-3">
                            <div class="d-flex menu-childrens-custom">
                                @foreach($cate['childrens'] as $groupKey => $groupItems)
                                    {{-- Lọc ra các item con thực sự có is_menu = true --}}
                                    @php
                                        $filteredGroupItems = collect($groupItems)->filter(fn($item) => !empty($item['is_menu']));
                                    @endphp

                                    @if($filteredGroupItems->isNotEmpty())
                                        <div class="mb-1">
                                            <div class="text-uppercase fw-6 mb-1 fs-15" style=" color: #333;">
                                                {{ $groupTitle($groupKey) }}
                                            </div>

                                            <div class="row gy-2 flex-column">
                                                @foreach($filteredGroupItems as $cateChild)
                                                    <div class="position-relative py-1 text-start d-flex align-items-center menu-childrens-custom-item">
                                                        @if(!empty($cateChild['image']))
                                                            <img src="{{ $cateChild['image'] }}" alt="{{ $cateChild['name'] }}" width="26" height="26" class="d-lg-block ratio-1x1 me-2 pointer-events-none">
                                                        @endif
                                                        <a class="stretched-link text-000 text-dc0021-hover d-block text-decoration-none" href="{{ $cateChild['url'] }}" title="{{ $cateChild['name'] }}">
                                                            {{ $cateChild['name'] }}
                                                            {!! $renderBadge($cateChild) !!}
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
@endif
