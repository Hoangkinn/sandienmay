<style>
    .select2-container {
        width: 100% !important;
    }
</style>
<x-app-layout>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Sửa người dùng') }}: {{ $user->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('Trang chủ') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.user.index') }}">{{ __('Danh sách người dùng') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Sửa') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('backend.user.update', $user->id) }}">
            @csrf
            @method('PUT')
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Thông tin') }}</h3>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">{{ __('Họ tên') }} <code>*</code></label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Họ tên" autofocus>
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">{{ __('Email') }} <code>*</code></label>
                                    <input type="text" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Địa chỉ email">
                                    @error('email')
                                    <span id="email-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">{{ __('Mật khẩu mới') }}</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Để trống nếu không muốn đổi mật khẩu">
                                    @error('password')
                                    <span id="password-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">{{ __('Nhập lại mật khẩu mới') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" placeholder="Nhập lại mật khẩu mới">
                                    @error('password_confirmation')
                                    <span id="password_confirmation-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Quyền chính') }} <code>*</code></label>
                                    
                                    @php
                                        // Giải mã role nếu nó lưu dạng JSON hoặc chuỗi thông thường
                                        $roleDecoded = json_decode($user->role, true);
                                        $currentRole = is_array($roleDecoded) ? ($roleDecoded['role'] ?? '') : $user->role;
                                    @endphp

                                    <select class="form-control select2" name="role" id="roleMainSelect">
                                        @foreach ($roleList as $role)
                                            @if ($role->value !== 'super_admin')
                                                <option value="{{ $role->value }}" {{ (old('role', strtolower($currentRole)) == strtolower($role->value)) ? 'selected' : '' }}>
    {{ $role->label() }}
</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div id="permissionBox" style="display: none;">
                                    <hr>
                                    <label class="mb-3">{{ __('Phân quyền chi tiết') }}</label>
                                    @include('backend.user.permission')
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.user.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Tác vụ') }}</h3>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('backend.user.index') }}" class="btn btn-secondary">{{ __('Quay lại') }}</a>
                                {{-- <button type="submit" name="submit" value="save" class="btn btn-primary"><i class="fa fa-save mr-1"></i>{{ __('Lưu') }}</button> --}}
                                <button type="submit" name="submit" value="save_continue" class="btn btn-success"><i class="fa fa-redo mr-1"></i>{{ __('Lưu và tiếp tục') }}</button>
                            </div>
                        </div>
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Options') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="status">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                            <option value="{{ $status->value }}" {{ (old('status', $user->status->value ?? $user->status) == $status->value) ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
    const select = document.getElementById('roleMainSelect');
    const box = document.getElementById('permissionBox');
    
    // Lưu lại giá trị ban đầu thực sự từ Blade trước khi Select2 can thiệp
    const initialRole = select ? select.value : '';

    // Khởi tạo Select2
    $('.select2').select2();

    // Khôi phục lại chính xác giá trị ban đầu cho Select2 sau khi khởi tạo
    if (initialRole && typeof jQuery !== 'undefined') {
        $('#roleMainSelect').val(initialRole).trigger('change.select2');
    }

    const roleModulesMap = @json($roleModulesMap);

    function handleRoleChange() {
        if (!select || !box) return;

        const currentRole = select.value.toLowerCase(); // Chuyển về lowercase để so sánh an toàn

        if (currentRole === 'admin' || currentRole === 'super_admin') {
            box.style.setProperty('display', 'none', 'important');
            return;
        } else {
            box.style.setProperty('display', 'block', 'important');
        }

        const allowedModules = roleModulesMap[select.value] || [];
        const moduleCards = box.querySelectorAll('.permission-card');

        moduleCards.forEach(card => {
            const moduleName = card.getAttribute('data-module');
            
            if (allowedModules.includes(moduleName)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
                card.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.checked = false;
                });
            }
        });
    }

    // Lắng nghe sự kiện thay đổi của select
    if (typeof jQuery !== 'undefined') {
        $('#roleMainSelect').on('select2:select change', function() {
            handleRoleChange();
        });
    } else {
        select.addEventListener('change', handleRoleChange);
    }
    
    // Gọi chạy sau khi đã ép lại giá trị chuẩn
    handleRoleChange();
});
    </script>
    @endpush

</x-app-layout>