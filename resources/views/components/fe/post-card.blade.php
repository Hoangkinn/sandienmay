@props(['post'])

<a {{ $attributes->merge(['class' => 'post-card']) }} href="{{ $post['url'] }}">
    <div class="post-card-thumb">
        @if (!empty($post['image']))
            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" width="318" height="238" loading="lazy">
        @endif
    </div>
    <h3 class="post-card-title">{{ $post['title'] }}</h3>
    <time class="post-card-date" datetime="{{ \Carbon\Carbon::parse($post['date'])->format('Y-m-d') }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        </svg>
        {{ \Carbon\Carbon::parse($post['date'])->format('d/m/Y') }}
    </time>
</a>
