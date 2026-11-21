<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Quản lý trang') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Quản lý trang') }}</li>
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
            <form method="POST" action="{{ route('backend.page.index') }}">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin tìm kiếm</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tiêu đề</label>
                                    <input type="text" name="title" value="{{ old('title') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select class="form-control select2">
                                        <option value="1" selected="selected">{{ __('Hiển thị') }}</option>
                                        <option value="0">{{ __('Ẩn') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info float-right">Tìm kiếm</button>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.page.create') }}" class="btn btn-success float-left">{{ __('Thêm mới') }}</a>
                    <div class="card-tools">
                        {{ $pages->onEachSide(5)->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">#</th>
                                <th style="width: 20%">{{ __('Tiêu đề') }}</th>
                                <th style="width: 20%">{{ __('Ngày đăng') }}</th>
                                <th style="width: 20%">{{ __('Trang thái') }}</th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pages as $page)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $page->title }}</td>
                                <td>{{ $page->created_at }}</td>
                                <td>
                                    <span class="badge badge-{{ $page->status == App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">{{ $page->status == App\Enums\Status::ACTIVED->value ? 'Hiển thị' : 'Ẩn' }}</span>
                                </td>
                                <td class="project-actions text-right">
                                    <a target="_blank" class="btn btn-primary btn-sm" href="{{ route('frontend.slug', ['slug' => $page->slug]) }}">
                                        <i class="fas fa-eye"></i>
                                        {{ __('Xem trên web') }}
                                    </a>
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.page.edit', ['id' => $page->id]) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa trang này?')" href="{{ route('backend.page.delete', ['id' => $page->id]) }}">
                                        <i class="fas fa-trash"></i>
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
                    {{ $pages->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>