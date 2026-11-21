<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Quản lý tin tức') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Quản lý tin tức') }}</li>
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
            <form method="GET" action="{{ route('backend.post.index') }}">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin tìm kiếm</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- 1. Tách riêng ô Tiêu đề (hay dùng nhất) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tiêu đề bài viết</label>
                                    <input type="text" name="title" value="{{ request('title') }}" placeholder="Nhập tiêu đề..." class="form-control">
                                </div>
                            </div>

                            <!-- 2. Ô tìm kiếm theo SEO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tìm theo SEO (Title, Keywords, Desc)</label>
                                    <input type="text" name="seo_keyword" value="{{ request('seo_keyword') }}" placeholder="Nhập từ khóa SEO..." class="form-control">
                                </div>
                            </div>

                            <!-- 3. Lọc trạng thái -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select class="form-control select2" name="status">
                                        <option value="">-- Tất cả trạng thái --</option>
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}" @selected($status->value == request('status'))>
                                            {{ $status->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- 4. Tìm theo Nội dung -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Tìm theo Nội dung</label>
                                    <input type="text" name="excerpt" value="{{ request('excerpt') }}" placeholder="Tìm trong đoạn mô tả đã soạn thảo..." class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info float-right">Lọc</button>
                    </div>
                </div>
            </form>
            <form method="GET" action="{{ route('backend.post.index') }}" id="postStatusFilterForm" class="d-none">
                <input type="hidden" name="title" value="{{ request('title') }}">
                <input type="hidden" name="seo_keyword" value="{{ request('seo_keyword') }}">
                <input type="hidden" name="excerpt" value="{{ request('excerpt') }}">
                <input type="hidden" name="status" id="postFormStatusInput">
            </form>

            <div class="text-muted mt-1 mb-3" style="font-size: 18px;">
                Tổng 
                <a href="javascript:void(0);" onclick="submitPostStatusFilter('')" class="btn {{ empty(request('status')) ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $totalPosts ?? 0 }}
                </a> 
                - Hiển thị 
                <a href="javascript:void(0);" onclick="submitPostStatusFilter('actived')" class="btn {{ request('status') === 'actived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $activePosts ?? 0 }}
                </a> 
                - Ẩn 
                <a href="javascript:void(0);" onclick="submitPostStatusFilter('inactived')" class="btn {{ request('status') === 'inactived' ? 'btn-warning' : 'btn-primary border' }}" style="padding: 0 6px; font-weight: 600; text-decoration: none;">
                    {{ $hiddenPosts ?? 0 }}
                </a>
            </div>

            <script>
                function submitPostStatusFilter(statusVal) {
                    document.getElementById('postFormStatusInput').value = statusVal;
                    document.getElementById('postStatusFilterForm').submit();
                }
            </script>
            <div class="card">
                <form action="{{ route('backend.post.delete-multiple') }}" method="POST" id="form-delete-multiple">
                @csrf
                <div class="card-header">
                    <a href="{{ route('backend.post.create') }}" class="btn btn-success float-left">{{ __('Thêm mới') }}</a>
                    <div class="card-tools">
                        {{-- {{ $posts->onEachSide(5)->links('backend.paginator') }} --}}
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteMultiple()">
                            <i class="fas fa-trash"></i> {{ __('Xóa các mục đã chọn') }}
                        </button> 
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 20px;">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>#</th>
                                <th>{{ __('Hình ảnh') }}</th>
                                <th>{{ __('Tiêu đề') }}</th>
                                <th>{{ __('Nổi bật') }}</th>
                                <th>{{ __('Ngày đăng') }}</th>
                                <th>{{ __('Trang thái') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($posts as $post)
                            <tr>
                                <td><input type="checkbox" name="ids[]" class="sub-checkbox" value="{{ $post->id }}">
                                </td>
                                <td>{{ $post->id }}</td>
                                <td><img src="{{asset('storage/'.$post->image )}}" title="{{ $post->title }}" class="img-size-120" /></td>
                                <td>
                                    {{ $post->title }}
                                    <p>
                                        STT: <input type="text" size="3" class="update-order-{{ $post->id }} text-center" value="{{ $post->order }}" post-id="{{ $post->id }}" onchange="updateOrder(this)">
                                    </p>
                                </td>
                                <td>
                                    <input type="checkbox" 
                                        class="toggle-featured" 
                                        post-id="{{ $post->id }}" 
                                        {{ $post->is_featured ? 'checked' : '' }} 
                                        onchange="updateFeatured(this)">
                                </td>
                                <td>{{ $post->created_at }}</td>
                                <td>
                                    <span class="badge badge-{{ $post->status == App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">{{ $post->status == App\Enums\Status::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}</span>
                                </td>
                                <td class="project-actions text-right">
                                    <a target="_blank" class="btn btn-primary btn-sm" href="{{ route('frontend.slug', ['slug' => $post->slug]) }}">
                                        <i class="fas fa-eye"></i>
                                        {{ __('Xem trên web') }}
                                    </a>
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.post.edit', ['id' => $post->id]) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa bài viết này?')" href="{{ route('backend.post.delete', ['id' => $post->id]) }}">
                                        <i class="fas fa-trash"></i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $posts->onEachSide(5)->appends($_GET)->links('backend.paginator') }}
                </div>
                </form>
            </div>
        </div>
    </section>
    @push('scripts')
    <script>
        function updateOrder(elem) {
            let token = document.querySelector('meta[name="csrf-token"]').content;
            let id = $(elem).attr('post-id');
            let order = $(elem).val();
            let route = `/backend/ajax/update-order/post/${id}/order/${order}`;
            $(elem).prop('disabled', true);
            $.post(route, {'_token': token}, function (response) {
                if ('messages' in response) {
                    toastr.success(response.messages);
                }
            }).always(function () {
                $(elem).prop('disabled', false);
            });
        }

        function updateFeatured(elem) {
            let token = document.querySelector('meta[name="csrf-token"]').content;
            let id = $(elem).attr('post-id');
            let isFeatured = $(elem).is(':checked') ? 1 : 0;
            let route = `/backend/post/ajax/update-featured/${id}`;
            
            $(elem).prop('disabled', true);
            
            $.post(route, {'_token': token, 'is_featured': isFeatured}, function (response) {
                toastr.success('Đã cập nhật thành công!');
            }).fail(function() {
                toastr.error('Có lỗi xảy ra');
                $(elem).prop('checked', !$(elem).is(':checked')); 
            }).always(function () {
                $(elem).prop('disabled', false);
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

        function confirmDeleteMultiple() {
            let checkedCount = document.querySelectorAll('.sub-checkbox:checked').length;

            if (checkedCount === 0) {
                alert('Vui lòng chọn ít nhất một bài viết để xóa!');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn xóa ' + checkedCount + ' bài viết đã chọn không?')) {
                // Submit form đi
                document.getElementById('form-delete-multiple').submit();
            }
        }
    </script>
    @endpush
</x-app-layout>