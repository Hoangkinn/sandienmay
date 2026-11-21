<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Tạo mới câu hỏi') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách câu hỏi') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Tạo mới') }}</h3>
                        </div>

                        <form method="POST" action="{{ route('backend.faq.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="question">{{ __('Câu hỏi') }} <code>*</code></label>
                                    <input type="text" name="question" class="form-control" id="name" placeholder="Câu hỏi" required autofocus>
                                </div>
                                <div class="form-group">
                                    <label for="answer">{{ __('Trả lời') }} <code>*</code></label>
                                    <textarea id="answer" name="answer" class="form-control" placeholder="Trả lời"></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.faq.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Tạo mới') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @push('scripts')
    <script>
        $(function() {
            $('.select2').select2();

            tinymceInit.selector = '#answer';
            tinymceInit.file_picker_callback = function (cb, value, meta) {
                var url = "{{ route('backend.photo.store') }}";
                uploadImg(url, cb, value, meta);
            };

            tinymce.init(tinymceInit);
        });
    </script>
    @endpush
</x-app-layout>