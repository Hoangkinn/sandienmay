<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách thuộc tính') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách thuộc tính') }}</li>
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
                        <form class="input-group input-group-sm" style="width: 250px;" method="GET" action="{{ route('backend.attribute.index') }}">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control float-right" placeholder="Nhập tên thuộc tính">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <a href="{{ route('backend.attribute.create') }}" class="btn btn-success card-tools">{{ __('Tạo mới') }}</a>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">Id</th>
                                <th style="width: 20%">{{ __('Tên thuộc tính') }}</th>
                                <th style="width: 20%">{{ __('Đường dẫn') }}</th>
                                <th style="width: 10%">{{ __('Thứ tự') }}</th>
                                <th style="width: 10%">{{ __('Trạng thái') }}</th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attributes as $attribute)
                            <tr>
                                <td>{{ $attribute->id }}</td>
                                <td>{{ $attribute->name }}</td>
                                <td>{{ $attribute->slug }}</td>
                                <td>{{ $attribute->position }}</td>
                                <td>
                                    <span class="badge badge-{{ $attribute->status->value == App\Enums\Status::ACTIVED->value ? 'success' : 'danger' }}">
                                        {{ $attribute->status->label() }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.attribute.edit', ['id' => $attribute->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa thuộc tính này?')" href="{{ route('backend.attribute.delete', ['id' => $attribute->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
                                        {{ __('Xóa') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">Không có dữ liệu</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>