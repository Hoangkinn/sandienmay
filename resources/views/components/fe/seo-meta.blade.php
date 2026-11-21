<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
{{-- <meta name="robots" content="{{ $robots }}"> --}}
<meta name="robots" content="{{ $robots ?? 'noindex,nofollow' }}">
<link rel="canonical" href="{{ $url }}">

<!-- Facebook Open Graph -->
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
@if($image)
<meta property="og:image" content="{{ $image }}">
@endif
<meta property="og:url" content="{{ $url }}">
<meta property="og:type" content="website">

<!-- Twitter Card -->
{{--<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">--}}
