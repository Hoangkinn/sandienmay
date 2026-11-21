@php
    $breadcrumbs = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => $titleHome,
            'item' => $urlHome,
        ]
    ];
    $count = 1;
@endphp

<div class="breadcrumbs">
    <div class="container">
        <div class="breadcrumbs-main d-flex gap10">
            <a class="breadcrumbs-home d-flex gap05" href="{{ $urlHome }}" title="{{ $titleHome }}">
                Trang chủ</a>
            @foreach($items as $url => $title)
                @php
                    $breadcrumbs[] = [
                        '@type' => 'ListItem',
                        'position' => ++$count,
                        'name' => $title,
                        'item' => $url,
                    ];
                @endphp
                <div class="breadcrumbs-icon">
                        <img src="images/ic-right.svg" width="8" height="14" alt="Mũi tên phân cách breadcrumb">
                    </div>
                <a class="breadcrumbs-link" href="{{ $url }}">{{ $title }}</a>
            @endforeach
        </div>
    </div>
</div>

