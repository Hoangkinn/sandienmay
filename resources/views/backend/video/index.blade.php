<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách video') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách video') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.video.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                    <div class="card-tools">
                        {{ $videos->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>{{ __('Tiêu đề') }}</th>
                                <th>{{ __('Link') }}</th>
                                <th>{{ __('Ngày sửa') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($videos as $video)
                            <tr>
                                <td>{{ $video->id }}</td>
                                <td>{{ $video->title }}</td>
                                <td> <a href="{{ $video->url }}" target="_blank">{{ $video->url }}</a></td>
                                <td>
                                    Người cập nhật: {{ $video->user ? $video->user->name : '' }}
                                    <br>
                                    Cập nhật: {{ $video->updated_at }}
                                    <br>
                                    Trạng thái: 
                                    <span class="badge bg-{{ $video->status == App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">
                                        {{ $video->status == App\Enums\Status::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.video.edit', ['id' => $video->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa video này?')" href="{{ route('backend.video.delete', ['id' => $video->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $videos->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>