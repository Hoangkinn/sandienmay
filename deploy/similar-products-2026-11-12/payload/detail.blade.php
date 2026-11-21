@php use App\Helpers\BreadcrumbHelper; 
$wishlist = session('wishlist', []);
@endphp
@extends('web.layout')

@if($product['images'])
@push('preload')
<link rel="preload" href="{{ $product['images'][0] }}" as="image" fetchpriority="high">
@endpush
@endif

@section('content')
{!! BreadcrumbHelper::render() !!}

@include('web.product._first-screen', ['showFull' => true, 'preload' => true])
<style>
    .content-custom .d-flex.my-2 {
        display: block !important;
    }

    .customA a {
        color: #0D7CFD;
        font-weight: 600;
    }

    .customA a:hover {
        color: #ED1B24 !important;
    }
   
</style>
@php
    $productCount = count($recentProducts);
    $rows = ceil($productCount / 2);
    
    $calculatedHeight = $rows * 300; 
    
    if($calculatedHeight < 600) $calculatedHeight = 600;
@endphp

<div class="container g-0 pd-body">
    <div class="row g-0 g-lg-3">
        <div class="col-lg-9 order-2 order-lg-1">
            {{-- ============ 1. NỘI DUNG CHÍNH ============ --}}
            @if(!empty($product['content']))
            <div class="pd-card">
                <div class="content content-custom content-maxH customA pd-content" style="--content-maxH: {{ $calculatedHeight }}px; max-height: {{ $calculatedHeight }}px; overflow: hidden;">
                    @include('components/fe/toc', ['toc' => $product['toc'] ?? []])
                    {!! $product['content'] !!}
                </div>
            </div>
            @endif

            {{-- ============ 2. ĐÁNH GIÁ ============ --}}
            <div class="pd-card pd-reviews">
                <h2 class="pd-card-title">Đánh giá {{ $product['name'] }}</h2>

                <div class="pd-rate-summary">
                    <div class="pd-score">
                        <div class="pd-score-num">{{ $reviewAvg }}/5</div>
                        <x-fe.show-vote :avg="$reviewAvg"></x-fe.show-vote>
                        <div class="pd-score-count">{{ $reviewTotal }} đánh giá</div>
                    </div>
                    <div class="pd-bars" id="statisticReview">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $countByStar = $voteStats[$i] ?? 0;
                                $percent = $reviewTotal > 0 ? ($countByStar / $reviewTotal) * 100 : 0;
                            @endphp
                            <div class="pd-bar-row">
                                <span class="pd-bar-label">{{ $i }}<i class="icon-star-full"></i></span>
                                <div class="progressProduct rounded-pill overflow-hidden">
                                    <div class="progress-bar" style="width: {{ round($percent) }}%"></div>
                                </div>
                                <span class="countVote">{{ round($percent) }}%</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <form id="originalCommentForm" method="post" action="" class="pd-form" data-onsuccess="resetCommentForm">
                    <div class="pd-form-rate">
                        <span>Bạn cảm thấy sản phẩm này như thế nào?</span>
                        <x-fe.vote required="true" />
                    </div>

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
                        <span>Bình luận</span>
                        <textarea required rows="4" name="content" placeholder="Nhập bình luận/nhận xét của bạn"></textarea>
                    </label>

                    <div class="pd-form-actions">
                        <button type="submit">Gửi đánh giá</button>
                    </div>
                </form>

                <div class="comment-list-container pd-comment-list">
                    @foreach($reviews as $i)
                    <div class="pd-comment">
                        <div class="pd-comment-head">
                            <span class="pd-avatar"><i class="icon-account_circle"></i></span>
                            <div>
                                <div class="pd-comment-name">
                                    <strong>{{ $i['name'] ?? $i['author'] }}</strong>
                                    <span class="pd-time"><i class="icon-clock"></i>{{ \Carbon\Carbon::parse($i['created_at'])->diffForHumans() }}</span>
                                </div>
                                <x-fe.show-vote :avg="$i['rating']"></x-fe.show-vote>
                            </div>
                        </div>
                        <div class="pd-comment-text">{!! nl2br(e($i['content'])) !!}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ============ 3. HỎI VÀ ĐÁP ============ --}}
            <div class="pd-card pd-qa">
                <h2 class="pd-card-title">Hỏi và đáp</h2>
                <div class="pd-qa-note">
                    {{ config('app.name') }} sẽ trả lời trong vòng 1 giờ (sau 19h, phản hồi vào sáng hôm sau). Một số
                    thông tin có thể thay đổi, Quý khách vui lòng đặt câu hỏi để được cập nhật mới nhất.
                </div>

                <div class="pd-qa-actions">
                    <button type="button" id="btnShowAskForm">Gửi bình luận</button>
                </div>

                <form id="askCommentForm" method="post" action="" class="pd-form pd-qa-form" data-onsuccess="resetCommentForm">
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
                        <textarea required rows="3" name="content" placeholder="Đặt câu hỏi cho điện máy"></textarea>
                    </label>
                    <div class="pd-form-actions">
                        <button type="submit">Gửi bình luận</button>
                    </div>
                </form>

                <div class="pd-qa-list">
                    @foreach($questions as $qi => $q)
                    <div class="pd-qa-item{{ $qi >= 2 ? ' is-hidden' : '' }}">
                        <div class="pd-qa-head">
                            <span class="pd-avatar pd-avatar-letter">{{ mb_strtoupper(mb_substr($q['author'] ?? '?', 0, 1)) }}</span>
                            <strong>{{ $q['author'] }}</strong>
                        </div>
                        <div class="pd-qa-text">{!! nl2br(e($q['content'])) !!}</div>
                        <div class="pd-qa-meta">
                            <span class="pd-reply-link">Trả lời</span>
                            <span class="pd-time"><i class="icon-clock"></i>{{ \Carbon\Carbon::parse($q['created_at'])->diffForHumans() }}</span>
                        </div>

                        @foreach($q['replies'] ?? [] as $r)
                        <div class="pd-qa-reply">
                            <div class="pd-qa-head">
                                @if(!empty($r['avatar']))
                                    <img class="pd-avatar-img" src="{{ $r['avatar'] }}" alt="Ảnh đại diện {{ $r['author'] ?? 'quản trị viên' }}" width="28" height="28">
                                @else
                                    <span class="pd-avatar pd-avatar-letter">{{ mb_strtoupper(mb_substr($r['author'] ?? '?', 0, 1)) }}</span>
                                @endif
                                <strong>{{ $r['author'] }}</strong>
                            </div>
                            <div class="pd-qa-text">{!! nl2br(e($r['content'])) !!}</div>
                            <div class="pd-qa-meta">
                                <span class="pd-reply-link">Trả lời</span>
                                <span class="pd-time"><i class="icon-clock"></i>{{ \Carbon\Carbon::parse($r['created_at'])->diffForHumans() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>

                @php $hiddenQuestions = max(0, count($questions) - 2); @endphp
                @if($hiddenQuestions > 0)
                <div class="pd-qa-more">
                    <button type="button" class="cta-xemthem-more" id="btnMoreQa" data-left="{{ $hiddenQuestions }}">
                        Xem thêm {{ $hiddenQuestions }} bình luận
                        <i class="icon-chevron-down"></i>
                    </button>
                </div>
                @endif
            </div>

            @if(!empty($news))
                <div class="related-by-parent mt-4 bg-fff p-20 rounded-10 customs-news-qt-mb">
                    <h3>Có thể bạn quan tâm</h3>
                    <div class="d-flex flex-column gap10 mt-3">
                        @foreach ($news as $item)
                            <a href="{{ $item['url'] }}" class="categories-news-item d-flex gap10">
                                <div>
                                    <img src="{{ $item['image'] }}" alt="Thumbnail bài viết: {{ $item['name'] }}" width="100" height="80">
                                </div>
                                <h3>
                                    <p>{{ $item['name'] }}</p>
                                </h3>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-3 order-1 order-lg-2">
            {{-- ============ THÔNG SỐ KỸ THUẬT ============ --}}
            @if(!empty($productAttributes))
            <div class="pd-card specs-container">
                <div class="pd-card-title">Thông số kỹ thuật</div>

                <div class="specs-content-wrapper collapsed" id="specsWrapper">
                    <table class="fs-14 w-100">
                        <tbody>
                            @foreach($productAttributes as $attr)
                            <tr>
                                <td>{{ $attr['name'] }}</td>
                                <td>
                                    {{ implode(', ', array_column($attr['options'], 'name')) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3" id="specsActionBox" style="display: none;">
                    <button type="button" class="cta-thongso" id="toggleSpecsBtn" onclick="toggleSpecs()">
                        <span id="toggleText">Xem thêm</span>
                        <i class="icon-chevron-down ms-1" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            @endif

            {{-- ============ SẢN PHẨM TƯƠNG TỰ ============ --}}
            @if(!empty($relateProducts))
            @php $similarProducts = array_slice($relateProducts, 0, 6); @endphp
            <div class="pd-card pd-similar">
                <div class="pd-card-title">Sản phẩm tương tự</div>
                <div class="pd-similar-list">
                    <x-fe.products :products="$similarProducts" :viewSoldQuantity="true" />
                </div>
            </div>
            @endif
        </div>
    </div>
</div>


@endsection

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js" defer></script>
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" media="print" onload="this.media='all'" /> --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy trực tiếp slug chuẩn của sản phẩm từ backend
        let realProductSlug = "{{ $product['slug'] ?? '' }}";

        // Nếu biến blade trống, lấy fallback từ đoạn cuối URL
        if (!realProductSlug) {
            let pathArray = window.location.pathname.split('/').filter(Boolean);
            realProductSlug = pathArray[pathArray.length - 1];
        }

        // Đưa slug vào route AJAX thay vì ID số
        let cleanActionUrl = "/ajax/comment/" + realProductSlug + "/product";
        window.endPointGetStatisticReview = "/ajax/rated/" + realProductSlug;

        ['originalCommentForm', 'askCommentForm'].forEach(function (formId) {
            let theForm = document.getElementById(formId);
            if (!theForm) return;
            theForm.setAttribute('action', cleanActionUrl);

            // Nếu form có chứa input ẩn lưu product_id/slug, cập nhật lại giá trị thành slug
            let inputProductSlug = theForm.querySelector('input[name="product_slug"]') || theForm.querySelector('input[name="product_id"]') || theForm.querySelector('input[name="id"]');
            if (inputProductSlug) {
                inputProductSlug.value = realProductSlug;
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        Common.loadJs('CustomGlide.min.js').then(() => {
            CustomGlide.initAll()
        });
    });
    
    function resetCommentForm(response, form) {
        form.reset(); 
        const stars = form.querySelectorAll('.vote-item.active');
        stars.forEach(s => s.classList.remove('active'));
        setTimeout(() => { 
            window.location.reload(); 
        }, 1000);
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ---- Thông số kỹ thuật: ẩn nút nếu bảng không tràn quá 500px ----
        const wrapper = document.getElementById('specsWrapper');
        const actionBox = document.getElementById('specsActionBox');

        if (wrapper && actionBox) {
            const maxHeight = 500;

            if (wrapper.scrollHeight > maxHeight) {
                actionBox.style.display = 'block';
            } else {
                wrapper.classList.remove('collapsed');
                actionBox.style.display = 'none';
            }
        }

        // ---- Hỏi và đáp: mở form gửi bình luận ----
        const askBtn = document.getElementById('btnShowAskForm');
        const askForm = document.getElementById('askCommentForm');
        if (askBtn && askForm) {
            askBtn.addEventListener('click', function () {
                const open = askForm.classList.toggle('is-open');
                askBtn.style.display = open ? 'none' : '';
                if (open) askForm.querySelector('input[name="author"]').focus();
            });
        }

        // ---- Hỏi và đáp: xem thêm các câu hỏi còn lại ----
        const moreBtn = document.getElementById('btnMoreQa');
        if (moreBtn) {
            moreBtn.addEventListener('click', function () {
                document.querySelectorAll('.pd-qa-item.is-hidden').forEach(el => el.classList.remove('is-hidden'));
                moreBtn.parentElement.style.display = 'none';
            });
        }
    });

    function toggleSpecs() {
        const wrapper = document.getElementById('specsWrapper');
        const toggleText = document.getElementById('toggleText');
        const toggleIcon = document.getElementById('toggleIcon');

        if (wrapper.classList.contains('collapsed')) {
            wrapper.classList.remove('collapsed');
            toggleText.innerText = 'Thu gọn';
            toggleIcon.classList.remove('icon-chevron-down');
            toggleIcon.classList.add('icon-chevron-up');
        } else {
            wrapper.classList.add('collapsed');
            toggleText.innerText = 'Xem thêm';
            toggleIcon.classList.remove('icon-chevron-up');
            toggleIcon.classList.add('icon-chevron-down');

            wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
</script>
@php
    $product_js_path = 'js-new/fe/pages/product.min.js';
    $product_js_ver = filemtime(public_path($product_js_path));
@endphp

<script type="text/javascript" src="{{ asset($product_js_path . '?v=' . $product_js_ver) }}"></script>
@endpush

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/product.min.css?v=' . filemtime(public_path('css-new/dist/product.min.css'))) }}">
@endpush
