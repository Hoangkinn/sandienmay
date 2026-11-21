@extends('web.layout')
@section('content')
<div class="breadcrumbs" style="margin-bottom: 30px">
    <div class="container">
        <div class="breadcrumbs-main d-flex gap10">
            <a class="breadcrumbs-home d-flex gap05" href="{{ route('frontend.page.home') }}">
                Trang chủ
            </a>
            <div class="breadcrumbs-icon">
                <img src="images/ic-right.svg" width="8" height="14" alt="Mũi tên phân cách breadcrumb">
            </div>
            <span class="breadcrumbs-link">{{$page['title']}}</span>
        </div>
    </div>
</div>
<div class="container g-1 g-lg-0 mb-4">
    <div class="row g-1 g-lg-4">
        <div class="col-lg-6">
            <div class="bg-fff rounded-10 p-10">
                {{-- <h1 class="my-0 fs-20 fw-6">{{ $page['title'] }}</h1> --}}
                <div class="zing-content">
                    {!! $page['content'] !!}
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bg-fff rounded-10 p-20">
                <div class="my-0 fs-20 fw-6 text-1F1B56">Liên hệ với chúng tôi</div>
                <div class="fs-14 fw-4 text-000">Nếu bạn có thắc mắc gì, có thể gửi yêu cầu cho chúng tôi, và chúng tôi sẽ liên lạc lại với bạn sớm nhất có thể .</div>
                <form method="post" action="{{ route('frontend.contact.store') }}"
                    class="contact-form"
                    data-onsuccess="resetContactForm">
                    <div>
                        <input required type="text"
                            class="col-12 rounded-5 border border-D9D9D9 p-5 mt-3 placeholder-a9a9a9"
                            name="name" placeholder="Nhập họ và tên" style="height: 40px">
                    </div>
                    <div>
                        <input required type="email"
                            class="col-12 rounded-5 border border-D9D9D9 p-5 mt-3 placeholder-a9a9a9"
                            name="email" placeholder="Nhập email" style="height: 40px">
                    </div>
                    <div>
                        <input required type="text"
                            class="col-12 rounded-5 border border-D9D9D9 p-5 mt-3 placeholder-a9a9a9"
                            name="phone" placeholder="Nhập số điện thoại" style="height: 40px">
                    </div>
                    <div>
                        <textarea required rows="5"
                            class="col-12 rounded-5 border border-D9D9D9 p-5 mt-3 placeholder-a9a9a9"
                            name="content" placeholder="Nhập nội dung"></textarea>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit"
                            class="fs-16 text-fff bg-d4281e border-0 rounded-pill cursor-pointer px-20 py-10 rounded-30 cta-D80000">Gửi
                            thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class=" contact-google-map mt-3">
        {!! $googleMap !!}
    </div>
</div>
@stop
@push('scripts')
<script>
    function resetContactForm(rs, form) {
        form.reset();
    }
</script>
@endpush

@push('page-css')
<link rel="stylesheet" href="{{ asset('css-new/dist/page.min.css?v=' . filemtime(public_path('css-new/dist/page.min.css'))) }}">
@endpush
