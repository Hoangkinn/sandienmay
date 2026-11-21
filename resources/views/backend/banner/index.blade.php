<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách banner') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách banner') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.banner.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">#</th>
                                <th style="width: 20%">{{ __('Hình ảnh destop') }}</th>
                                <th style="width: 20%">{{ __('Hình ảnh mobile') }}</th>
                                <th style="width: 20%">{{ __('Ngày tạo') }}</th>
                                <th style="width: 10%">{{ __('Số thứ tự') }}</th> <!-- Thêm tiêu đề cột số thứ tự -->
                                <th style="width: 10%">{{ __('Trạng thái') }}</th>
                                <th style="width: 19%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($banners as $banner)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><img src="{{asset('storage/'.$banner->image )}}" title="{{ $banner->title }}" class="img-size-64" /></td>
                                <td><img src="{{asset('storage/'.$banner->image_mobile )}}" width="50px" /></td>
                                <td>{{ $banner->created_at }}</td>
                                <td>
                                    <!-- Hiển thị giá trị order, nếu null thì hiện 0 hoặc để trống -->
                                    <span class="">{{ $banner->order ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $banner->status ==  App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">
                                        {{ $banner->status ==  App\Enums\Status::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.banner.edit', ['id' => $banner->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa banner này?')" href="{{ route('backend.banner.delete', ['id' => $banner->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">Không có dữ liệu</td> <!-- Đổi colspan thành 7 vì đã tăng thêm 1 cột -->
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>