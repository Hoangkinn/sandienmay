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
                    <h1>{{ __('Tạo người dùng') }}</h1>
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
        <form method="POST" action="{{ route('backend.user.store') }}">
            @csrf
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
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Họ tên" autofocus>
                                    @error('name')
                                    <span id="name-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">{{ __('Email') }} <code>*</code></label>
                                    <input type="text" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Địa chỉ email">
                                    @error('email')
                                    <span id="email-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">{{ __('Mật khẩu') }} <code>*</code></label>
                                    <input type="password" name="password" value="{{ old('password') }}" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Mật khẩu">
                                    @error('password')
                                    <span id="password-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">{{ __('Nhập lại mật khẩu') }} <code>*</code></label>
                                    <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" placeholder="Nhập lại mật khẩu">
                                    @error('password_confirmation')
                                    <span id="password_confirmation-error" class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Quyền chính') }} <code>*</code></label>
                                    <select class="form-control select2" name="role" id="roleMainSelect">
                                        @foreach ($roleList as $role)
                                            @if ($role->value !== 'super_admin')
                                                <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
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
                                    <label for="title">{{ __('Trạng thái') }} <code>*</code></label>
                                    <select class="form-control select2" name="status">
                                        @foreach ($statusList as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
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
            $('.select2').select2();

            const select = document.getElementById('roleMainSelect');
            const box = document.getElementById('permissionBox');

            // Lấy trực tiếp mảng cấu hình từ Controller truyền sang
            const roleModulesMap = @json($roleModulesMap);

            function handleRoleChange() {
                if (!select || !box) return;

                const currentRole = select.value;

                // Nếu là admin hoặc super_admin thì ẩn khung phân quyền đi
                if (currentRole === 'admin' || currentRole === 'super_admin') {
                    box.style.setProperty('display', 'none', 'important');
                    return;
                } else {
                    box.style.setProperty('display', 'block', 'important');
                }

                // Lấy danh sách tên module cho phép tương ứng với Role
                const allowedModules = roleModulesMap[currentRole] || [];
                
                // Lấy tất cả các thẻ Card module trong file permission partial
                const moduleCards = box.querySelectorAll('.permission-card');

                moduleCards.forEach(card => {
                    const moduleName = card.getAttribute('data-module');
                    
                    if (allowedModules.includes(moduleName)) {
                        card.style.display = ''; // Hiển thị card module này
                    } else {
                        card.style.display = 'none'; // Ẩn card module không thuộc quyền
                        
                        // Bỏ tích tất cả các checkbox bên trong card bị ẩn để dữ liệu gửi lên sạch sẽ
                        card.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                            cb.checked = false;
                        });
                    }
                });
            }

            // Gọi chạy ngay khi load trang
            handleRoleChange();

            // Lắng nghe sự kiện thay đổi của select
            if (typeof jQuery !== 'undefined') {
                $('#roleMainSelect').on('select2:select change', function() {
                    handleRoleChange();
                });
            } else {
                select.addEventListener('change', handleRoleChange);
            }
        });
    </script>
    @endpush

</x-app-layout>