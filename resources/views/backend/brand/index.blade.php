<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách thương hiệu') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách thương hiệu') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <form class="input-group input-group-sm" style="width: 250px;" method="GET" action="{{ route('backend.brand.index') }}">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control float-right" placeholder="Tìm thương hiệu...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <a href="{{ route('backend.brand.create') }}" class="btn btn-success btn-sm card-tools" style="margin-top: 5px;">{{ __('Tạo mới') }}</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 5%">Id</th>
                                <th style="width: 10%">{{ __('Logo') }}</th>
                                <th style="width: 25%">{{ __('Tên thương hiệu') }}</th>
                                <th style="width: 25%">{{ __('Danh mục tham chiếu') }}</th>
                                <th style="width: 15%">{{ __('Trạng thái') }}</th>
                                <th style="width: 30%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($brands as $brand)
                            <tr>
                                <td>{{ $brand->id }}</td>
                                <td>
                                        @if($brand->image)
                                            <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo" style="width: 50px; height: auto; object-fit: cover;">
                                        @else
                                            <span class="text-muted">Không có ảnh</span>
                                        @endif
                                </td>
                                <td>{{ $brand->name }}</td>
                                <td>
                                    @if($brand->categories->count() > 0)
                                        {{ $brand->categories->pluck('name')->implode(', ') }}
                                    @else
                                        <span class="text-muted">Chưa chọn</span>
                                    @endif
                                </td>           
                                <td>
                                    {{-- Giả sử bạn sử dụng trait IsActive hoặc thuộc tính tương tự --}}
                                    <span class="badge badge-{{ $brand->is_active ? 'success' : 'danger' }}">
                                        {{ $brand->is_active ? 'Hiển thị' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.brand.edit', ['id' => $brand->id]) }}">
                                        <i class="fas fa-pencil-alt"></i> {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa thương hiệu này?')" href="{{ route('backend.brand.delete', ['id' => $brand->id]) }}">
                                        <i class="fas fa-trash"></i> {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('Không có dữ liệu') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>