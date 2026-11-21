@extends('web.layout')

@section('body-class') post-v2 @endsection

@once
    @php
        $adminCustomSchema = null;
        if (isset($category)) {
            if (is_array($category)) {
                $adminCustomSchema = $category['schema'] ?? null;
            } elseif (is_object($category)) {
                $adminCustomSchema = $category->schema ?? null;
            }
        }
        $adminCustomSchema = is_string($adminCustomSchema) ? trim($adminCustomSchema) : null;
    @endphp

    {{-- Nếu admin có nhập custom schema -> Ẩn 3 schema cứng ở layout chính --}}
    @if(!empty($adminCustomSchema))
        @section('default-schema')
            {{-- Để trống để triệt tiêu 3 schema cứng --}}
        @endsection
    @endif

    @push('schema')
        @if(!empty($adminCustomSchema))
            {{-- Trường hợp 1: Nếu admin có nhập custom schema -> Chỉ hiển thị của admin --}}
            @if(str_starts_with($adminCustomSchema, '<script'))
                {!! $adminCustomSchema !!}
            @else
                <script type="application/ld+json">
                {!! $adminCustomSchema !!}
                </script>
            @endif
        @else
            {{-- Trường hợp 2: Nếu không nhập -> Tự động render BreadcrumbList cho trang danh mục bài viết --}}
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
                            'name' => $cat['name'] ?? $cat->name,
                            'item' => $cat['url'] ?? route('frontend.slug', ['slug' => $cat->slug]),
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
        $postV2Path = 'css-new/dist/post-v2.min.css';
        $postV2Ver = file_exists(public_path($postV2Path)) ? filemtime(public_path($postV2Path)) : time();
    @endphp
    <link rel="stylesheet"
        href="{{ asset($postV2Path . '?v=' . $postV2Ver) }}">
@endpush

@section('content')
    @php
        $childCats = collect($categories ?? [])->flatten(1)->filter()->values();

        // Chuỗi breadcrumb: cha -> con hiện tại (route danh mục bài viết không có $cateBr nên tự truy ngược parent)
        $crumbs = [];
        if (!empty($cateBr) && is_iterable($cateBr)) {
            foreach ($cateBr as $cat) {
                $crumbs[] = [
                    'name' => is_array($cat) ? ($cat['name'] ?? '') : ($cat->name ?? ''),
                    'url' => is_array($cat) ? ($cat['url'] ?? '') : ($cat->url ?? ''),
                ];
            }
        } elseif (!empty($category)) {
            $currentCat = $category;
            $collected = [];
            while ($currentCat) {
                $collected[] = [
                    'name' => is_array($currentCat) ? ($currentCat['name'] ?? '') : ($currentCat->name ?? ''),
                    'url' => route('frontend.slug', ['slug' => is_array($currentCat) ? ($currentCat['slug'] ?? '') : ($currentCat->slug ?? '')]),
                ];
                $parentId = is_array($currentCat) ? ($currentCat['parent_id'] ?? null) : ($currentCat->parent_id ?? null);
                $currentCat = $parentId
                    ? \DB::table('m_categories')->where('id', $parentId)->first()
                    : null;
            }
            $crumbs = array_reverse($collected);
        }
    @endphp

    <div class="container">
        <nav class="breadcrumb-bar" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Trang chủ</a></li>
                @foreach ($crumbs as $i => $crumb)
                    <li>
                        @if ($i === count($crumbs) - 1)
                            <span aria-current="page">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <x-fe.news-chips :categories="$childCats" :active-id="$category->id ?? null" />

        <section class="news-block">
            <div class="news-block-head">
                <x-fe.news-title title="Bài viết mới nhất" />
            </div>
            <div class="news-grid">
                @forelse ($posts as $post)
                    <x-fe.post-card :post="$post" />
                @empty
                    <p class="news-empty">Danh mục này chưa có bài viết nào.</p>
                @endforelse
            </div>
            @if (!empty($nextPageUrl))
                <x-fe.news-loadmore :after="count($posts)" :category="$category->slug ?? null" />
            @endif
        </section>
    </div>
@endsection
