<style>
    
.switch {
  position: relative;
  display: inline-block;
  width: 34px;
  height: 18px;
  vertical-align: middle;
  margin-bottom: 0;
  margin-left: 3px  
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 14px;
  width: 14px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(16px);
}
</style>
<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>{{ ($type == App\Enums\CategoryType::PRODUCT->value) ? __('Danh mục sản phẩm') : __('Danh mục tin tức') }}</h1>
            
            <!-- Dùng style="display: none;" thay cho d-none để chắc chắn nó ẩn đi -->
            <form method="GET" action="{{ route('backend.category.index', ['type' => $type]) }}" id="categoryStatusFilterForm" style="display: none;">
                <input type="hidden" name="status" id="categoryFormStatusInput">
            </form>

            <div class="text-muted mt-1" style="font-size: 18px;">
                Tổng 
                <a href="javascript:void(0);" onclick="submitCategoryStatusFilter('')" class="btn {{ empty(request('status')) ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $totalCategories }}
                </a> 
                - Hiển thị 
                <a href="javascript:void(0);" onclick="submitCategoryStatusFilter('actived')" class="btn {{ request('status') === 'actived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $activeCategories }}
                </a> 
                - Ẩn 
                <a href="javascript:void(0);" onclick="submitCategoryStatusFilter('inactived')" class="btn {{ request('status') === 'inactived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $hiddenCategories }}
                </a>
            </div>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                <li class="breadcrumb-item active">
                    {{ ($type == App\Enums\CategoryType::PRODUCT->value) ? __('Danh mục sản phẩm') : __('Danh mục tin tức') }}
                </li>
            </ol>
        </div>
    </div>
</div>

<script>
    function submitCategoryStatusFilter(statusVal) {
        document.getElementById('categoryFormStatusInput').value = statusVal;
        document.getElementById('categoryStatusFilterForm').submit();
    }
</script>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-warning btn-save-order float-left">Lưu thứ tự
                        sắp
                        xếp</button>
                    <a href="{{ route('backend.category.create', ['type' => $type]) }}"
                        class="btn btn-success float-right">{{ __('Tạo mới danh mục') }}</a>
                </div>
                <div class="card-body">
                    <menu id="nestable-menu">
                        <button type="button" class="btn btn-primary" data-action="expand-all">Mở tất cả</button>
                        <button type="button" class="btn btn-primary" data-action="collapse-all">Thu gọn tất
                            cả</button>
                    </menu>

                    <div class="dd" id="nestable" data-url="{{ route('backend.category.update-sort', ['type' => $type]) }}">
                        <ol class="dd-list">
                            @foreach ($categories as $category)
                            <li class="dd-item" data-id="{{ $category->id }}" data-children="">
                                <div class="dd-handle">
                                    <span>{{ $category->name }} ({{ $type == App\Enums\CategoryType::PRODUCT->value ? $category->countProduct() : $category->countPost() }})</span>
                                    <div class="float-right">
                                        <span class="dd-nodrag mr-3">
                                            @if($type == App\Enums\CategoryType::PRODUCT->value)
                                                Menu
                                                <label class="switch">
                                                    <input type="checkbox" class="toggle-is-menu" 
                                                        data-id="{{ $category->id }}" 
                                                        {{ $category->is_menu ? 'checked' : '' }}>
                                                    <span class="slider"></span>
                                                </label>
                                            @endif
                                        </span>
                                        {!! $category->getStatusName() !!}
                                        <a href="{{ route('frontend.slug', ['slug' => $category->slug]) }}" target="_blank"
                                            title="Xem trang" class="dd-nodrag ml-2">
                                            Xem trang
                                        </a>
                                        <a href="{{ route('backend.category.edit', ['type' => $type, 'id' => $category->id]) }}"
                                            class="dd-nodrag ml-2">
                                            Sửa
                                        </a>
                                        <a class="text-danger table-btn-delete dd-nodrag ml-2"
                                            onclick="return confirm('Bạn muốn xóa danh mục này?')"
                                            href="{{ route('backend.category.delete', ['type' => $type, 'id' => $category->id]) }}">
                                            Xóa
                                        </a>
                                    </div>
                                </div>
                                @if(count($category->childs))
                                <ol class="dd-list">
                                    @include('backend.category.child.nestable', [
                                    'childs' => $category->childs
                                    ])
                                </ol>
                                @endif
                            </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-warning btn-save-order">Lưu thứ tự
                        sắp
                        xếp</button>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
    $(function() {
        $('#nestable').nestable({
            maxDepth: 4,
        }).nestable('collapseAll');

        $('#nestable-menu').on('click', function(e) {
            var target = $(e.target),
                action = target.data('action');
            if (action === 'expand-all') {
                $('.dd').nestable('expandAll');
            }
            if (action === 'collapse-all') {
                $('.dd').nestable('collapseAll');
            }
        });

        $('.btn-save-order').on('click', function() {
            let menu = $('#nestable');
            let json = JSON.stringify(menu.nestable('serialize'));
            let post_data = {
                'json': json,
                '_token': $('meta[name="csrf-token"]').attr('content')
            };
            $.post(menu.data("url"), post_data, function(response) {
                toastr.success(response.message);
            });
        });
    });

    // Thêm vào trong block $(function() { ... });
    $('.toggle-is-menu').on('change', function() {
        let checkbox = $(this);
        let categoryId = checkbox.data('id');
        let isMenu = checkbox.is(':checked') ? 1 : 0;

        $.post("{{ route('backend.category.toggle-menu') }}", {
            'id': categoryId,
            'is_menu': isMenu,
            '_token': $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error('Có lỗi xảy ra!');
                checkbox.prop('checked', !checkbox.is(':checked')); // Hoàn tác nếu lỗi
            }
        });
    });

    $(document).on('change', '.badge-select', function() {
        let select = $(this);

        $.post("{{ route('backend.category.update-badge') }}", {
            'id': select.data('id'),
            'badge': select.val(),
            '_token': $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error('Có lỗi xảy ra!');
            }
        });
    });
    </script>
    @endpush
</x-app-layout>