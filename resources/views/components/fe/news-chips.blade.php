@props(['categories', 'activeId' => null])

<nav class="news-chips">
    @foreach ($categories as $cat)
        <a href="{{ $cat['url'] }}"
            class="news-chip {{ (int) ($cat['id'] ?? 0) === (int) $activeId ? 'is-active' : '' }}">{{ $cat['name'] }}</a>
    @endforeach
</nav>
