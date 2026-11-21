<div class="row">
    @foreach ($permissionGroups as $columnIndex => $columns)
        <div class="col-md-6">
            @php 
                $stt = 1;
                if ($columnIndex > 0) {
                    for ($i = 0; $i < $columnIndex; $i++) {
                        $stt += count($permissionGroups[$i]);
                    }
                }
            @endphp

            @foreach ($columns as $moduleName => $permissions)
                <div class="card card-outline card-primary mb-3 permission-card" data-module="{{ $moduleName }}">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold" style="font-size: 15px;">
                            {{ $stt }}. {{ $moduleName }}
                        </h3>
                        
                        <!-- Thêm checkbox Chọn tất cả cho riêng card này -->
                        <div class="card-tools d-flex align-items-center" style="position: absolute;
    right: 15px;">
                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" class="custom-control-input select-all-module" id="selectAll_{{ $columnIndex }}_{{ $loop->index }}">
                                <label class="custom-control-label font-weight-normal" for="selectAll_{{ $columnIndex }}_{{ $loop->index }}" style="cursor: pointer; font-size: 15px;line-height: 26px;">
                                    Chọn tất cả
                                </label>
                            </div>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body py-2">
                        <div class="row">
                            @foreach ($permissions as $permission)
                                @php
                                    $permValue = is_object($permission) ? $permission->value : $permission;
                                    $permName = is_object($permission) && method_exists($permission, 'label') ? $permission->label() : $permission;
                                @endphp
                                
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        {{-- Quan trọng: name="permissions[]" chỉ dành cho các quyền chi tiết --}}
                                        <input type="checkbox" 
                                            class="custom-control-input permission-item" 
                                            id="perm_{{ $columnIndex }}_{{ $loop->parent->index }}_{{ $loop->index }}" 
                                            name="permissions[]" 
                                            value="{{ $permValue }}"
                                            {{ in_array($permValue, old('permissions', $userPermissions ?? [])) ? 'checked' : '' }}>
                                        
                                        <label class="custom-control-label font-weight-normal" 
                                            for="perm_{{ $columnIndex }}_{{ $loop->parent->index }}_{{ $loop->index }}" 
                                            style="cursor: pointer; font-size: 15px;">
                                            {{ $permName }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php $stt++; @endphp
            @endforeach
        </div>
    @endforeach
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    // Xử lý sự kiện click vào checkbox "Chọn tất cả" của từng module
    document.querySelectorAll('.select-all-module').forEach(selectAllCheckbox => {
        // Kiểm tra trạng thái ban đầu xem các ô con đã được chọn hết chưa để check ô tổng
        const cardBody = selectAllCheckbox.closest('.permission-card').querySelector('.card-body');
        const itemCheckboxes = cardBody.querySelectorAll('.permission-item');
        
        if (itemCheckboxes.length > 0) {
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }

        // Lắng nghe sự kiện thay đổi
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            itemCheckboxes.forEach(cb => {
                cb.checked = isChecked;
            });
        });

        // Nếu người dùng bỏ bớt 1 ô con thì tự động bỏ check ô "Chọn tất cả"
        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(itemCheckboxes).every(item => item.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    });
});
</script>