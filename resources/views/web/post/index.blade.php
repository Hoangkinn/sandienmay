@extends('web.layout')

@section('body-class') post-v2 @endsection

@push('schema')
    @php
        $breadcrumbJson = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => route('frontend.page.home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Tin tức',
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
{!! json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

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
        // $categories từ controller là mảng cặp (chia 2 cột của layout cũ) ->flatten để có danh sách con phẳng
        $childCats = collect($categories ?? [])->flatten(1)->filter()->values();
    @endphp

    <div class="container">
        <nav class="breadcrumb-bar" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Trang chủ</a></li>
                <li><span aria-current="page">Tin tức</span></li>
            </ol>
        </nav>

        <x-fe.news-chips :categories="$childCats" />

        <section class="news-block">
            <div class="news-block-head">
                <x-fe.news-title title="Bài viết mới nhất" />
            </div>
            <div class="news-grid">
                @forelse ($posts as $post)
                    <x-fe.post-card :post="$post" />
                @empty
                    <p class="news-empty">Danh mục chưa có bài viết nào.</p>
                @endforelse
            </div>
            @if (!empty($nextPageUrl))
                <x-fe.news-loadmore :after="count($posts)" />
            @endif
        </section>

        @foreach ($childCats as $cat)
            @if (count($cat['posts'] ?? []) > 0)
                <section class="news-block">
                    <div class="news-block-head">
                        <x-fe.news-title :title="$cat['name']" />
                        <a href="{{ $cat['url'] }}" class="news-more">Xem thêm
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                    <div class="news-grid">
                        @foreach ($cat['posts'] as $post)
                            <x-fe.post-card :post="$post" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
@endsection
