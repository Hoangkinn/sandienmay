<x-app-layout>
    <div class="container-fluid px-4 ">
        <h3 class="" style="padding: 1.5rem 0 1rem">Lịch sử hoạt động</h3>

        {{-- Form bộ lọc tìm kiếm --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-1"></i> Bộ lọc tìm kiếm
            </div>
            <div class="card-body">
                <form action="{{ route('backend.history.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Người thực hiện</label>
                        <select name="user_id" class="form-control">
                            <option value="">-- Tất cả thành viên --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name ?? $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Hành động</label>
                        <select name="action" class="form-control">
                            <option value="">-- Tất cả hành động --</option>
                            <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Thêm mới (Create)</option>
                            <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Cập nhật (Update)</option>
                            <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Xóa (Delete)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Từ khóa mô tả</label>
                        <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Nhập nội dung tìm kiếm...">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2"><i class="fas fa-search"></i> Lọc</button>
                        <a href="{{ route('backend.history.index') }}" class="btn btn-secondary" style="margin-left: 5px"><i class="fas fa-redo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bảng hiển thị danh sách lịch sử --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i> Danh sách nhật ký thao tác
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">Thời gian</th>
                                <th width="15%">Người thực hiện</th>
                                <th width="10%">Hành động</th>
                                <th width="15%">Đối tượng</th>
                                <th width="35%">Mô tả chi tiết</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ $history->id }}</td>
                                    <td>{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $history->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($history->action == 'create')
                                            <span class="badge badge-success">Thêm mới</span>
                                        @elseif($history->action == 'update')
                                            <span class="badge badge-primary">Cập nhật</span>
                                        @elseif($history->action == 'restore' || str_contains($history->description, 'Khôi phục'))
                                            <span class="badge badge-warning text-dark font-weight-bold">
                                                <i class="fas fa-undo"></i> Khôi phục dữ liệu
                                            </span>
                                        @elseif($history->action == 'delete')
                                            <span class="badge badge-danger">Xóa</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $history->action }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $history->model_type }} (ID: {{ $history->model_id }})</td>
                                    <td>{{ $history->description }}</td>
                                    <td>
                                        <a href="{{ route('backend.history.show', $history->id) }}" class="btn btn-sm btn-light" title="Xem chi tiết thay đổi">
                                            <i class="fas fa-search text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có lịch sử hoạt động nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Phân trang --}}
                <div class="card-footer clearfix">
                    {{ $histories->onEachSide(5)->links('backend.paginator') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>