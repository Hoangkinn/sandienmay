<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Dự án đã thực hiện') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Dự án đã thực hiện') }}</li>
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

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.project.create') }}" class="btn btn-success float-left">{{ __('Thêm mới') }}</a>
                    <div class="card-tools">
                        {{ $projects->onEachSide(5)->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Hình ảnh') }}</th>
                                <th>{{ __('Tên dự án') }}</th>
                                <th>{{ __('Trang thái') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>
                                    @if ($project->imageFirst())
                                    <img src="{{ $project->imageFirst()->url }}" title="{{ $project->name }}" class="img-size-120" />
                                    @endif
                                </td>
                                <td>{{ $project->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $project->status == App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">{{ $project->status == App\Enums\Status::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}</span>
                                </td>
                                <td class="project-actions text-right">
                                    <a target="_blank" class="btn btn-primary btn-sm" href="{{ route('frontend.slug', ['slug' => $project->slug]) }}">
                                        <i class="fas fa-eye"></i>
                                        {{ __('Xem trên web') }}
                                    </a>
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.project.edit', ['id' => $project->id]) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa dự án này?')" href="{{ route('backend.project.delete', ['id' => $project->id]) }}">
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
                    {{ $projects->onEachSide(2)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>