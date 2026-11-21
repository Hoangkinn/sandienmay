<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Cấu hình') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Cấu hình') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <a href="{{ route('backend.setting-field.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                    <div class="card-tools">
                        {{ $settingFields->onEachSide(5)->links('backend.paginator') }}
                    </div>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Field name') }}</th>
                                <th>{{ __('Field key') }}</th>
                                <th>{{ __('Field type') }}</th>
                                <th>{{ __('Cập nhật') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody> 
                            @forelse ($settingFields as $settingField)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $settingField->field_name }}</td>
                                <td>{{ $settingField->field_key }}</td>
                                <td>{{ $settingField->field_type->label() }}</td>
                                <td>{{ \Carbon\Carbon::parse($settingField->updated_at)->format('d-m-Y H:i:s') }}</td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.setting-field.edit', ['id' => $settingField->id]) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        {{ __('Sửa') }}
                                    </a>

                                    <a href="{{ route('backend.setting-field.delete', ['id' => $settingField->id]) }}" 
                                        class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa cấu hình này không?');">
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
                    {{ $settingFields->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>