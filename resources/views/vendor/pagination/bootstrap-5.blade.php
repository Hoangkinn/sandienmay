@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-center gap-2 gap-lg-3 py-20">
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <div class="bg-f00 px-13 py-5">{{ $element }}</div>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <div class="bg-f00 text-fff px-13 py-5">{{ $page }}</div>
                    @else
                        <a class="px-13 py-5 bg-fff bg-f00-hover text-000 text-fff-hover" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
