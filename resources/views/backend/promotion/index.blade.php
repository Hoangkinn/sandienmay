<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Quản lý khuyến mại') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Quản lý khuyến mại') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.promotion.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                    <div class="card-tools">
                        {{ $promotions->onEachSide(5)->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Tiêu đề') }}</th>
                                <th>{{ __('Thời gian hoạt động') }}</th>
                                <th>{{ __('Ngày tạo') }}</th>
                                <th>{{ __('Ngày cập nhật') }}</th>
                                <th>{{ __('Trạng thái') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($promotions as $promotion)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $promotion->title }}</td>
                                <td>
                                    <small>Từ ngày: <b>{{ $promotion->from_date }}</b></small><br>
                                    <small>Đến ngày: <b>{{ $promotion->to_date }}</b></small><br>
                                </td>
                                <td>{{ ($promotion->created_at)->format('d-m-Y H:i:s') }}</td>
                                <td>{{ ($promotion->updated_at)->format('d-m-Y H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-{{ $promotion->status == App\Enums\UserStatus::ACTIVED->value ? 'success' : 'danger' }}">
                                        {{ $promotion->status == App\Enums\UserStatus::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.promotion.edit', ['id' => $promotion->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa khuyến mại này?')" href="{{ route('backend.promotion.delete', ['id' => $promotion->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $promotions->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>