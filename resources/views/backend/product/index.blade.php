<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách sản phẩm') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách sản phẩm') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    @if (session('error'))
    <div class="alert alert-danger" role="alert">
        {{ session('error') }}
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <section class="content">
        <div class="container-fluid">
            <div class="filters">
                <form method="GET" action="{{ route('backend.product.index') }}" class="d-flex">
                    @csrf
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">Tên sản phẩm</label>
                        <input type="text" name="name" value="{{ old('name', $request->name) }}" class="form-control form-control-sm" placeholder="Tên sản phẩm">
                    </div>
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">Danh mục</label>
                        <select class="form-control form-control-sm select2 select2-blue" multiple name="category_id[]" data-placeholder="- Lựa chọn -" data-allow-clear="true">
                            <option value=""></option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->selectMultiple(old('category_id', $request->category_id)) }}>
                                    {{ $category->name }}
                                </option>
                                @if(count($category->childs))
                                    @include('backend.category.child.select-multiple', [
                                        'childs' => $category->childs,
                                        'expandable' => '--',
                                        'category_ids' => $request->category_id,
                                    ])
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">Đặc điểm</label>
                        <select class="form-control form-control-sm select2 select2-blue" name="featured" data-placeholder="- Lựa chọn -" data-allow-clear="true">
                            <option value=""></option>
                            @foreach ($featuredList as $featured)
                            <option value="{{ $featured->value }}" @selected($featured->value == $request->featured)>
                                {{ $featured->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">Trạng thái kho hàng</label>
                        <select class="form-control form-control-sm select2 select2-blue" name="stock_status" data-placeholder="- Lựa chọn -" data-allow-clear="true">
                            <option value=""></option>
                            @foreach ($productStatusList as $productStatus)
                            <option value="{{ $productStatus->value }}" @selected($productStatus->value == $request->stock_status)>
                                {{ $productStatus->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">Trạng thái</label>
                        <select class="form-control form-control-sm select2 select2-blue" name="status" data-placeholder="- Lựa chọn -" data-allow-clear="true">
                            <option value=""></option>
                            @foreach ($statusList as $status)
                            <option value="{{ $status->value }}" @selected($status->value == $request->status)>
                                {{ $status->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-1">
                        <label class="col-form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
            <!-- Form riêng biệt cho Tổng / Hiển thị / Ẩn -->
            <form method="GET" action="{{ route('backend.product.index') }}" id="statusFilterForm" class="d-none">
                <input type="hidden" name="name" value="{{ $request->name }}">
                @if($request->has('category_id'))
                    @foreach((array)$request->category_id as $catId)
                        <input type="hidden" name="category_id[]" value="{{ $catId }}">
                    @endforeach
                @endif
                <input type="hidden" name="featured" value="{{ $request->featured }}">
                <input type="hidden" name="stock_status" value="{{ $request->stock_status }}">
                <input type="hidden" name="status" id="formStatusInput">
            </form>

            <div class="text-muted mt-1 mb-3" style="font-size: 18px;">
                Tổng 
                <a href="javascript:void(0);" onclick="submitStatusFilter('')" class="btn {{ empty($request->status) ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $totalProducts ?? 0 }}
                </a> 
                - Hiển thị 
                <a href="javascript:void(0);" onclick="submitStatusFilter('actived')" class="btn {{ $request->status === 'actived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $activeProducts ?? 0 }}
                </a> 
                - Ẩn 
                <a href="javascript:void(0);" onclick="submitStatusFilter('inactived')" class="btn {{ $request->status === 'inactived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $hiddenProducts ?? 0 }}
                </a>
            </div>

            <script>
                function submitStatusFilter(statusVal) {
                    document.getElementById('formStatusInput').value = statusVal;
                    document.getElementById('statusFilterForm').submit();
                }
            </script>
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <a href="{{ route('backend.product.create') }}" class="btn btn-success float-left">{{ __('Thêm mới') }}</a>
                    {{-- <div class="card-tools">
                        {{ $products->onEachSide(5)->links('backend.paginator') }}
                    </div> --}}
                    <div class="card-tools">
                        {{-- {{ $posts->onEachSide(5)->links('backend.paginator') }} --}}
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteMultiplePrd()">
                            <i class="fas fa-trash"></i> {{ __('Xóa các mục đã chọn') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 20px;">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>id</th>
                                <th>{{ __('Hình ảnh') }}</th>
                                <th>{{ __('Tên sản phẩm') }}</th>
                                <th>{{ __('Người xem') }}</th>
                                <th>{{ __('Thông tin bán hàng') }}</th>
                                <th>{{ __('Ngày sửa') }}</th>
                                <th>{{ __('Đặc điểm') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                            <tr>
                                <td><input type="checkbox" name="ids[]" class="sub-checkbox" value="{{ $product->id }}"></td>
                                <td>{{ $product->id }}</td>
                                <td>
                                    @forelse ($product->imagePrimary as $image)
                                    <img src="{{ $image->url }}" title="{{ $product->name }}" class="img-size-120" />
                                    @empty
                                    <img src="{{asset('storage/' . $product->image) }}" title="{{ $product->name }}" class="img-size-120" />
                                    @endforelse
                                </td>
                                <td>
                                    <a target="_blank" href="{{ route('frontend.slug', ['slug' => $product->slug]) }}">
                                        {{ $product->name }}
                                    </a>
                                    <p>
                                        STT: <input type="text" size="2" class="update-order-{{ $product->id }} text-center" value="{{ $product->order }}" product-id="{{ $product->id }}" onchange="updateOrder(this)">
                                    </p>
                                </td>
                                <td>
                                    Xem: <b>{{ $product->view }}</b> <br>
                                    Mua: <b>{{ $product->getCountOrder() }}</b> <br>
                                </td>
                                <td>
                                    @if ($product->getPrice())
                                    Giá thị trường: <b>{{ $product->getPrice() }}đ</b><br>
                                    @endif

                                    @if ($product->getPrice3() || $product->getSalePrice())
                                    Giá bán: <b class="text-red price-format"> {{ $product->getPrice3() ? $product->getPrice3() : $product->getSalePrice() }}đ</b> <br>
                                    @endif
                                    
                                    @if ($product->quantity)
                                    SL tổng: {{ $product->quantity }}
                                    @endif
                                </td>
                                <td>
                                    Người tạo: {{ optional($product->createdUser)->name }} <br>
                                    Ngày đăng: {{ \Carbon\Carbon::parse($product->created_at)->format('d-m-Y H:i:s') }} <br>
                                    {{-- Người tạo: {{ $product->createdUser->name }} <br> --}}
                                    Cập nhật: {{ \Carbon\Carbon::parse($product->updated_at)->format('d-m-Y H:i:s') }} <br>
                                    Trạng thái: {!! $product->getStatusName() !!}<br>
                                    Trạng thái kho hàng: {!! $product->getStockStatusName() !!} <br>
                                    Mã kho: <b>{{ $product->model }}</b><br>
                                    Danh mục: @foreach ($product->categories as $category)
                                    <b class="cat-name">{{ $category->name }}</b>
                                    @endforeach
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('backend.product.updateFeature', ['product_id' => $product->id]) }}">
                                        @csrf
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input featured_new" id="is_new{{ $product->id }}" name="is_new" type="checkbox" value="1" @checked(old('is_new', $product->is_new))>
                                            <label for="is_new{{ $product->id }}" class="custom-control-label form-check-label" style="white-space: nowrap;">Mới</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input featured_hot" id="is_hot{{ $product->id }}" name="is_hot" type="checkbox" value="1" @checked(old('is_hot', $product->is_hot))>
                                            <label for="is_hot{{ $product->id }}" class="custom-control-label form-check-label" style="white-space: nowrap;">Best seller</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input featured_flash_sale" id="is_flash_sale{{ $product->id }}" name="is_flash_sale" type="checkbox" value="1" @checked(old('is_flash_sale', $product->is_flash_sale))>
                                            <label for="is_flash_sale{{ $product->id }}" class="custom-control-label form-check-label" style="white-space: nowrap;">Flash sale</label>
                                        </div>
                                        <button type="submit" class="btn-warning border-0 mt-1">Cập nhật</button>
                                        <span class="d-block"></span>
                                    </form>
                                </td>
                                <td class="project-actions text-left">
                                    <a class="bg-primary pl-1 pr-1" href="{{ route('frontend.slug', ['slug' => $product->slug]) }}" target="_blank">
                                        {{ __('Xem trên web') }}
                                    </a><br>
                                    <a class="bg-primary pl-1 pr-1" href="{{ route('backend.product.edit', ['id' => $product->id]) }}">
                                        {{ __('Sửa') }}
                                    </a><br>
                                    <a class="bg-warning pl-1 pr-1" href="javascript:updateStatus({{ $product->id }}, `{{ $product->status->value == App\Enums\Status::ACTIVED->value ? App\Enums\Status::INACTIVED->value : App\Enums\Status::ACTIVED->value }}`)" target="_self">
                                        {{ $product->status->value == App\Enums\Status::ACTIVED->value ? 'Ẩn sản phẩm' : 'Hiển thị sản phẩm' }}
                                    </a>
                                    <br>
                                    @if ($product->stock_status->value == App\Enums\ProductStatus::IN_STOCK->value)
                                    <a class="bg-warning pl-1 pr-1 " href="javascript:updateStock({{ $product->id }}, 'outofstock')" target="_self">
                                        Cập nhật hết hàng
                                    </a><br>
                                    @else
                                    <a class="bg-success pl-1 pr-1 " href="javascript:updateStock({{ $product->id }}, 'instock')" target="_self">
                                        Cập nhật còn hàng
                                    </a><br>
                                    @endif
                                    <a class="bg-danger pl-1 pr-1 table-btn-delete" onclick="return confirm('Bạn muốn xóa sản phẩm này?')" href="{{ route('backend.product.delete', ['id' => $product->id]) }}">
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $products->onEachSide(5)->appends($_GET)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
    @push('scripts')
    <script>
        $('.select2').select2();
        function updateOrder(elem) {
            let token = document.querySelector('meta[name="csrf-token"]').content;
            let id = $(elem).attr('product-id');
            let order = $(elem).val();
            let route = `/backend/ajax/update-order/product/${id}/order/${order}`;
            $(elem).prop('disabled', true);
            $.post(route, {
                '_token': token
            }, function(response) {
                if ('messages' in response) {
                    toastr.success(response.messages);
                }
                location.reload();
            }).always(function() {
                $(elem).prop('disabled', false);
            });
        }

        function updateStatus(id, status) {
            let token = document.querySelector('meta[name="csrf-token"]').content;
            let route = `/backend/ajax/update-status/product/${id}/${status}`;
            $.post(route, {
                '_token': token
            }, function(response) {
                if ('messages' in response) {
                    toastr.success(response.messages);
                }
                location.reload();
            });
        }

        function updateStock(id, status) {
            let token = document.querySelector('meta[name="csrf-token"]').content;
            let route = `/backend/ajax/update-stock/${id}/${status}`;
            $.post(route, {
                '_token': token
            }, function(response) {
                if ('messages' in response) {
                    toastr.success(response.messages);
                }
                location.reload();
            });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Logic chọn tất cả
            let selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    let subCheckboxes = document.querySelectorAll('.sub-checkbox');
                    subCheckboxes.forEach(cb => cb.checked = this.checked);
                });
            }
        });

        function confirmDeleteMultiplePrd() {
            let ids = [];
            // Lấy tất cả các checkbox sản phẩm đang được tích chọn
            $('.sub-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm để xóa!');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn xóa các sản phẩm đã chọn không?')) {
                // Tạo một form ẩn động để gửi dữ liệu chuẩn xác lên server
                let form = document.createElement('form');
                form.action = "{{ route('backend.product.delete-multiple') }}";
                form.method = 'POST';

                // Thêm CSRF Token bảo mật của Laravel
                let csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Đưa danh sách ID vào form dưới dạng mảng ids[]
                ids.forEach(id => {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
</x-app-layout>