@props(['toc', 'title' => 'Nội dung chính', 'open' => false])
<style>
.toc.posts-table-of-contends li {
    list-style: none;
}
</style>
<div class="toc posts-table-of-contends">
    <details class="bg-none" @if($open) open @endif>
        <summary class="fw-6">
            <div class="d-flex justify-content-between align-items-center cursor-pointer">
                <span class="fs-17">{{ $title }}</span>
                <div>
                    <span class="btn-hide"><i class="icon-chevron-down"></i></span>
                    <span class="btn-show"><i class="icon-chevron-up"></i></span>
                </div>
            </div>
        </summary>
        {{-- Dùng thẻ ul để tự kiểm soát số hiển thị bằng code PHP, không dùng ol mặc định nữa --}}
        <ul id="toc" class="text-007fc0 ps-15 ps-lg-30 m-0 mt-2 list-unstyled">
            {{-- @foreach($toc as $indexH2 => $h2) --}}
            @foreach($toc ?? [] as $indexH2 => $h2)
                @php
                    $cleanH2Title = preg_replace('/^\d+(\.\d+)*\s*[\.\-]?\s*/u', '', $h2['title']);
                    $numberH2 = $indexH2 + 1;
                @endphp
                <li class="mb-1">
                    <span class="cursor-pointer" data-scroll="#{{ $h2['id'] }}">{{ $numberH2 }}. {{ $cleanH2Title }}</span>
                    
                    @if(! empty($h2['children']))
                        <ul class="ps-15 ps-lg-30 list-unstyled mt-1">
                            @foreach($h2['children'] as $indexH3 => $h3)
                                @php
                                    $cleanH3Title = preg_replace('/^\d+(\.\d+)*\s*[\.\-]?\s*/u', '', $h3['title']);
                                    $numberH3 = $indexH3 + 1;
                                @endphp
                                <li class="mb-1">
                                    <span class="cursor-pointer" data-scroll="#{{ $h3['id'] }}">{{ $numberH2 }}.{{ $numberH3 }} {{ $cleanH3Title }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </details>
</div>