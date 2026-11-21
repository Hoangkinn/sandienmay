@extends('web.layout')

@section('body-class') post-v2 post-detail @endsection

@once
    @php
        $adminCustomSchema = null;
        if (isset($post)) {
            if (is_array($post)) {
                $adminCustomSchema = $post['schema'] ?? null;
            } elseif (is_object($post)) {
                $adminCustomSchema = $post->schema ?? null;
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
            {{-- Trường hợp 1: Chỉ hiển thị schema tùy chỉnh do admin nhập --}}
            @if(str_starts_with($adminCustomSchema, '<script'))
                {!! $adminCustomSchema !!}
            @else
                <script type="application/ld+json">
                {!! $adminCustomSchema !!}
                </script>
            @endif
        @else
            {{-- Trường hợp 2: Tự động render BlogPosting và BreadcrumbList chuẩn SEO --}}
            @php
                $postTitle = $post['seo_title'] ?? ($post->seo_title ?? ($post['title'] ?? ($post->title ?? '')));
                $postDesc = $post['seo_description'] ?? ($post->seo_description ?? '');
                $postImage = $post['image'] ?? ($post->image ?? '');
                if ($postImage && !str_starts_with($postImage, 'http')) {
                    $postImage = asset($postImage);
                }
                $postUrl = $post['url'] ?? ($post->url ?? url()->current());

                $blogPostingJson = [
                    "@context" => "https://schema.org",
                    "@type" => "BlogPosting",
                    "headline" => $postTitle,
                    "description" => $postDesc,
                    "image" => [
                        "@type" => "ImageObject",
                        "url" => $postImage,
                    ],
                    "author" => [
                        "@type" => "Organization",
                        "name" => config('app.name'),
                    ],
                    "publisher" => [
                        "@type" => "Organization",
                        "name" => config('app.name'),
                        "logo" => [
                            "@type" => "ImageObject",
                            "url" => $logo ?? '',
                        ],
                    ],
                    "datePublished" => isset($post['created_at']) ? \Carbon\Carbon::parse($post['created_at'])->toIso8601String() : null,
                    "dateModified" => isset($post['updated_at']) ? \Carbon\Carbon::parse($post['updated_at'])->toIso8601String() : null,
                    "mainEntityOfPage" => [
                        "@type" => "WebPage",
                        "@id" => $postUrl,
                    ],
                ];

                if (!empty($post['seo_keywords'] ?? ($post->seo_keywords ?? ''))) {
                    $keywordsStr = $post['seo_keywords'] ?? ($post->seo_keywords ?? '');
                    $blogPostingJson["keywords"] = array_map('trim', explode(',', $keywordsStr));
                }

                $breadcrumbItems = [
                    [
                        "@type" => "ListItem",
                        "position" => 1,
                        "name" => "Trang chủ",
                        "item" => url('/'),
                    ]
                ];

                $position = 2;
                if (!empty($similarCategories) && is_iterable($similarCategories)) {
                    foreach ($similarCategories as $cat) {
                        $breadcrumbItems[] = [
                            "@type" => "ListItem",
                            "position" => $position,
                            "name" => $cat->name,
                            "item" => route('frontend.slug', ['slug' => $cat->slug]),
                        ];
                        $position++;
                    }
                }

                $breadcrumbItems[] = [
                    "@type" => "ListItem",
                    "position" => $position,
                    "name" => $postTitle,
                    "item" => $postUrl,
                ];

                $breadcrumbJson = [
                    "@context" => "https://schema.org",
                    "@type" => "BreadcrumbList",
                    "itemListElement" => $breadcrumbItems,
                ];
            @endphp

            <script type="application/ld+json">
            {!! json_encode($blogPostingJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
            <script type="application/ld+json">
            {!! json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
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
        $postCats = collect($similarCategories ?? [])->filter();
        $authorName = $post['author'] ?: 'Admin';
        // Khối giới thiệu tác giả lấy từ Cấu hình (backend > Cấu hình), fallback về tác giả của bài viết
        $cardAuthor = ($postAuthorName ?? '') ?: $authorName;
        $authorBio = $postAuthorBio ?: 'Chia sẻ kinh nghiệm chọn mua và bảo dưỡng thiết bị công nghiệp, giúp khách hàng chọn đúng model phù hợp nhu cầu và ngân sách.';
    @endphp

    <div class="container">
        <nav class="breadcrumb-bar" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ url('/') }}">Trang chủ</a></li>
                @foreach ($postCats as $cat)
                    <li><a href="{{ route('frontend.slug', ['slug' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                @endforeach
                <li><span aria-current="page">{{ $post['title'] }}</span></li>
            </ol>
        </nav>

        <div class="pdv2">
            <article class="pdv2-main">
                <h1 class="pdv2-title">{{ $post['title'] }}</h1>

                <div class="pdv2-meta">
                    <span class="pdv2-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
                        </svg>
                        Đăng bởi <b>{{ $authorName }}</b>
                    </span>
                    <span class="pdv2-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                        Cập nhật {{ \Carbon\Carbon::parse($post['updated_at'])->format('d/m/Y') }}
                    </span>
                    <span class="pdv2-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round">
                            <path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z" />
                            <circle cx="12" cy="12" r="2.6" />
                        </svg>
                        {{ number_format($post['count_view'], 0, ',', '.') }} lượt xem
                    </span>
                </div>

                <div class="content pdv2-content">
                    <x-fe.toc :toc="$post['toc']" title="Mục lục" :open="true" />
                    {!! $post['content'] !!}
                </div>

                <div class="pdv2-author">
                    <div class="pdv2-author-avatar">
                        <span>{{ mb_strtoupper(mb_substr($cardAuthor, 0, 1)) }}</span>
                    </div>
                    <div class="pdv2-author-info">
                        <span class="pdv2-author-role">{{ $postAuthorRole ?: config('app.name') }}</span>
                        <strong class="pdv2-author-name">{{ $cardAuthor }}</strong>
                        <p>{{ $authorBio }}</p>
                        <div class="pdv2-social">
                            @if (!empty($linkYoutube))
                                <a href="{{ $linkYoutube }}" target="_blank" rel="nofollow" aria-label="Youtube">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23 12s0-3.9-.5-5.7a3 3 0 0 0-2.1-2.1C18.6 3.7 12 3.7 12 3.7s-6.6 0-8.4.5A3 3 0 0 0 1.5 6.3C1 8.1 1 12 1 12s0 3.9.5 5.7a3 3 0 0 0 2.1 2.1c1.8.5 8.4.5 8.4.5s6.6 0 8.4-.5a3 3 0 0 0 2.1-2.1C23 15.9 23 12 23 12zM9.8 15.5v-7l6.1 3.5-6.1 3.5z" />
                                    </svg>
                                </a>
                            @endif
                            @if (!empty($linkFb))
                                <a href="{{ $linkFb }}" target="_blank" rel="nofollow" aria-label="Facebook">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5h1.65V4.6a22 22 0 0 0-2.4-.12c-2.35 0-3.95 1.43-3.95 4.07v2.27H7.6V14h2.8v8h3.1z" />
                                    </svg>
                                </a>
                            @endif
                            @if (!empty($linkTiktok))
                                <a href="{{ $linkTiktok }}" target="_blank" rel="nofollow" aria-label="Tiktok">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M16.6 5.8a4.5 4.5 0 0 1-1-2.8h-3v12.4a2.5 2.5 0 1 1-2.5-2.5c.26 0 .5.04.75.1V9.8a5.6 5.6 0 0 0-.75-.05 5.5 5.5 0 1 0 5.5 5.5V9.3a7.4 7.4 0 0 0 4.3 1.37V7.6a4.5 4.5 0 0 1-3.25-1.8z" />
                                    </svg>
                                </a>
                            @endif
                            @if (!empty($linkZalo))
                                <a href="{{ $linkZalo }}" target="_blank" rel="nofollow" aria-label="Zalo"><span>Zalo</span></a>
                            @endif
                        </div>
                    </div>
                </div>

                <section class="pdv2-qa">
                    <div class="qa-head">
                        <h2 class="qa-title">Hỏi và đáp</h2>
                        <div class="qa-actions">
                            <button type="button" id="btnShowAskForm">Đặt câu hỏi</button>
                        </div>
                    </div>
                    <div class="qa-note">
                        {{ config('app.name') }} sẽ phản hồi trong vòng 24 giờ. Bạn hãy đặt câu hỏi về nội dung bài
                        viết để được đội ngũ kỹ thuật tư vấn chi tiết hơn.
                    </div>

                    <form id="askCommentForm" method="post" action="" class="pd-form qa-form"
                        data-onsuccess="resetCommentForm">
                        <div class="pd-form-grid">
                            <label>
                                <span>Họ tên</span>
                                <input required type="text" name="author" placeholder="Nhập họ và tên">
                            </label>
                            <label>
                                <span>Số điện thoại</span>
                                <input required type="text" name="author_email" placeholder="Nhập số điện thoại">
                            </label>
                        </div>
                        <label class="pd-form-comment">
                            <span>Câu hỏi của bạn</span>
                            <textarea required rows="3" name="content" placeholder="Đặt câu hỏi cho bài viết"></textarea>
                        </label>
                        <div class="pd-form-actions">
                            <button type="submit">Gửi bình luận</button>
                        </div>
                    </form>

                    <div class="pd-qa-list">
                        @foreach ($questions as $qi => $q)
                            <div class="pd-qa-item{{ $qi >= 2 ? ' is-hidden' : '' }}">
                                <div class="pd-qa-head">
                                    <span class="pd-avatar pd-avatar-letter">{{ mb_strtoupper(mb_substr($q['author'] ?? '?', 0, 1)) }}</span>
                                    <strong>{{ $q['author'] }}</strong>
                                </div>
                                <div class="pd-qa-text">{!! nl2br(e($q['content'])) !!}</div>
                                <div class="pd-qa-meta">
                                    <span class="pd-time">{{ \Carbon\Carbon::parse($q['created_at'])->diffForHumans() }}</span>
                                </div>

                                @foreach ($q['replies'] ?? [] as $r)
                                    <div class="pd-qa-reply">
                                        <div class="pd-qa-head">
                                            @if (!empty($r['avatar']))
                                                <img class="pd-avatar-img" src="{{ $r['avatar'] }}" alt="Ảnh đại diện {{ $r['author'] ?? 'quản trị viên' }}" width="28"
                                                    height="28">
                                            @else
                                                <span class="pd-avatar pd-avatar-letter">{{ mb_strtoupper(mb_substr($r['author'] ?? '?', 0, 1)) }}</span>
                                            @endif
                                            <strong>{{ $r['author'] }}</strong>
                                            <span class="qa-badge">Quản trị viên</span>
                                        </div>
                                        <div class="pd-qa-text">{!! nl2br(e($r['content'])) !!}</div>
                                        <div class="pd-qa-meta">
                                            <span class="pd-time">{{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    @php $hiddenQuestions = max(0, count($questions) - 2); @endphp
                    @if ($hiddenQuestions > 0)
                        <div class="pd-qa-more">
                            <button type="button" class="cta-xemthem-more" id="btnMoreQa">
                                Xem thêm {{ $hiddenQuestions }} bình luận
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </section>
            </article>

            <aside class="pdv2-side">
                <h2 class="news-block-title no-icon">Bài viết liên quan</h2>
                <div class="rel-list">
                    @foreach ($posts as $relatedPost)
                        <a href="{{ $relatedPost->url }}" class="rel-item">
                            <div class="rel-thumb">
                                @if (!empty($relatedPost->image))
                                    <img src="{{ $relatedPost->image }}" alt="{{ $relatedPost->title }}" width="96"
                                        height="72" loading="lazy">
                                @endif
                            </div>
                            <div class="rel-body">
                                <h3 class="rel-title">{{ $relatedPost->title }}</h3>
                                <span class="rel-date">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($relatedPost->date)->format('d/m/Y') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Form hỏi - đáp bài viết: gửi theo id bài viết (cột post_id là số)
            const askForm = document.getElementById('askCommentForm');
            if (askForm) {
                askForm.setAttribute('action', '/ajax/comment/{{ $post['id'] }}/post');
            }

            const askBtn = document.getElementById('btnShowAskForm');
            if (askBtn && askForm) {
                askBtn.addEventListener('click', function () {
                    const open = askForm.classList.toggle('is-open');
                    askBtn.style.display = open ? 'none' : '';
                    if (open) askForm.querySelector('input[name="author"]').focus();
                });
            }

            const moreBtn = document.getElementById('btnMoreQa');
            if (moreBtn) {
                moreBtn.addEventListener('click', function () {
                    document.querySelectorAll('.pd-qa-item.is-hidden').forEach(el => el.classList.remove('is-hidden'));
                    moreBtn.parentElement.style.display = 'none';
                });
            }
        });

        function resetCommentForm(response, form) {
            form.reset();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    </script>
@endpush
