<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h1>{{ __('Thuộc tính sản phẩm ') }}</h1>
                </div>
                <div class="col-sm-8">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.product.index') }}">{{ __('Danh sách sản phẩm') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.product.edit', ['id' => $product->id]) }}">{{ $product->name }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Lưu thuộc tính') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.product.attribute.save', ['product_id' => $product->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        @foreach ($attributes as $attribute)
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-tag"></i> {{ $attribute->name }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-10">
                                        {{-- Chuyển sang dùng select với Ajax, chỉ render các option đã chọn sẵn trước đó --}}
                                        <select class="form-control select2-ajax" name="attribute_options[{{$attribute->id}}][]" multiple="multiple" data-url="{{ route('backend.product.getAttributeOptions', $attribute->id) }}">
    @if(isset($selectedOptions[$attribute->id]))
        @foreach ($selectedOptions[$attribute->id] as $opt)
            <option value="{{ $opt['id'] }}" selected>{{ $opt['name'] }}</option>
        @endforeach
    @endif
</select>
                                    </div>
                                    <div class="form-group col-2">
                                        <input type="text" name="positions[{{ $attribute->id }}]"
                                            value="{{ old('positions.' . $attribute->id, $productAttributes[$attribute->id] ?? '') }}" placeholder="Số thứ tự" class="form-control @error('position') is-invalid @enderror" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-md-3">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    @push('backend-css')
    <link rel="stylesheet" href="{{ asset('css/dropzone.min.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2-ajax').each(function() {
                var $element = $(this);
                var url = $element.data('url');

                if (!$element.hasClass('select2-hidden-accessible')) {
                    $element.select2({
                        tags: true,
                        ajax: {
                            url: url,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    keyword: params.term // Gửi từ khóa tìm kiếm lên server
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map(function (item) {
                                        return {
                                            id: item.id,
                                            text: item.name
                                        };
                                    })
                                };
                            },
                            cache: true
                        },
                        createTag: function (params) {
                            const term = $.trim(params.term);
                            if (term === '') return null;
                            return {
                                id: term,
                                text: term,
                                newTag: true
                            };
                        }
                    });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>