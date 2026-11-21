<x-app-layout>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Danh sách người dùng') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Danh sách người dùng') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('backend.user.create') }}" class="btn btn-success float-left">{{ __('Tạo mới') }}</a>
                </div>
                <div class="card-body p-0" style="display: block;">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">#</th>
                                <th style="width: 20%">{{ __('Họ tên') }}</th>
                                <th style="width: 20%">{{ __('Email') }}</th>
                                <th style="width: 20%">{{ __('Quyền') }}</th>
                                <th style="width: 20%">{{ __('Trạng thái') }}</th>
                                <th style="width: 20%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $idx => $user)
                            <tr>
                                <td>{{ ($idx + 1) }}</td>
                                <td><a>{{ $user->name }}</a></td>
                                <td><a>{{ $user->email }}</a></td>
                                {{-- <td><a>{{ $user->role->label() }}</a></td> --}}
                                <td>
                                    <a>
                                        @php
                                            $roleVal = $user->role;
                                            // Nếu role lưu dạng JSON hoặc string, giải mã lấy giá trị thật
                                            if (is_string($roleVal)) {
                                                $decoded = json_decode($roleVal, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $roleVal = $decoded['role'] ?? $roleVal;
                                                }
                                            } elseif (is_object($roleVal) && property_exists($roleVal, 'value')) {
                                                $roleVal = $roleVal->value;
                                            }

                                            // Tìm nhãn (label) từ Enum Role tương ứng
                                            $roleLabel = $roleVal;
                                            foreach (\App\Enums\Role::cases() as $r) {
                                                if ($r->value === $roleVal) {
                                                    $roleLabel = $r->label();
                                                    break;
                                                }
                                            }
                                        @endphp
                                        {{ $roleLabel }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->status == App\Enums\UserStatus::ACTIVED->value ? 'success' : 'danger' }}">
                                        {{ $user->status == App\Enums\UserStatus::ACTIVED->value ? 'Hoạt động' : 'Tạm Khóa' }}
                                    </span>
                                </td>
                                <td class="project-actions text-right">
                                    <a class="btn btn-info btn-sm" href="{{ route('backend.user.edit', ['id' => $user->id]) }}">
                                        <i class="fas fa-pencil-alt">
                                        </i>
                                        {{ __('Sửa') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa người dùng này?')" href="{{ route('backend.user.delete', ['id' => $user->id]) }}">
                                        <i class="fas fa-trash">
                                        </i>
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
                <div class="card-footer clearfix backend-pagination">
                    {{ $users->links('frontend.paginator') }}
                </div>
            </div> 
        </div>
    </section>

    <style>
        .backend-pagination .pagination {
            margin: 0;
            float: right;
        }
        .backend-pagination .pagination a, 
        .backend-pagination .pagination span {
            position: relative;
            display: block;
            padding: .25rem .5rem;
            margin-left: -1px;
            line-height: 1.5;
            color: #007bff;
            background-color: #fff;
            border: 1px solid #dee2e6;
            font-size: .9rem;
        }
        .backend-pagination .pagination span {
            z-index: 3;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .backend-pagination .pagination a:hover {
            color: #0056b3;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
    </style>
</x-app-layout>