@props(['after' => 0, 'category' => null, 'label' => 'Xem thêm bài viết'])

<div class="news-loadmore-wrap">
    <button type="button" class="news-loadmore" data-news-more data-after="{{ (int) $after }}"
        @if ($category) data-category="{{ $category }}" @endif>
        <span>{{ $label }}</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <path d="M6 9l6 6 6-6" />
        </svg>
    </button>
</div>

@once
    @push('scripts')
        <script>
            (function() {
                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('[data-news-more]');
                    if (!btn || btn.dataset.busy) return;

                    var grid = btn.closest('.news-block') && btn.closest('.news-block').querySelector('.news-grid');
                    if (!grid) return;

                    btn.dataset.busy = '1';
                    btn.disabled = true;

                    var params = new URLSearchParams({ after: btn.dataset.after || '0' });
                    if (btn.dataset.category) params.set('category', btn.dataset.category);

                    fetch('/ajax/news-posts?' + params.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            if (d.error) throw new Error(d.message || 'load more failed');

                            grid.insertAdjacentHTML('beforeend', d.html);
                            btn.dataset.after = String(Number(btn.dataset.after) + d.count);

                            if (!d.hasMore || !d.count) {
                                btn.remove();
                                return;
                            }

                            btn.disabled = false;
                            delete btn.dataset.busy;
                        })
                        .catch(function() {
                            btn.disabled = false;
                            delete btn.dataset.busy;
                        });
                });
            })();
        </script>
    @endpush
@endonce
