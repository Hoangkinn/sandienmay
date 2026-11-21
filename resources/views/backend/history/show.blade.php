<x-app-layout>
@php
    $formatValue = function($value) {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return html_entity_decode($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    };
@endphp

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">Chi tiết thay đổi: {{ $history->model_type }} (ID: {{ $history->model_id }})</h4>
                <span class="text-muted">Thời gian: {{ $history->created_at->format('d/m/Y H:i:s') }} | Người thực hiện: {{ $history->user->name ?? 'N/A' }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Dữ liệu Cũ -->
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white font-weight-bold">Dữ liệu Cũ</div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                @forelse($oldData as $key => $value)
                                    <div class="mb-3">
                                        <strong class="text-dark">{{ $key }}:</strong>
                                        <div class="p-2 bg-light border rounded diff-old-box" data-key="{{ $key }}">
                                            {{ $formatValue($value) }}
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center">Không có dữ liệu cũ (Thêm mới)</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Dữ liệu Mới -->
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white font-weight-bold">Dữ liệu Mới thay đổi</div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                @forelse($newData as $key => $value)
                                    <div class="mb-3">
                                        <strong class="text-dark">{{ $key }}:</strong>
                                        <div class="p-2 bg-light border rounded diff-new-box" data-key="{{ $key }}">
                                            {{ $formatValue($value) }}
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center">Không có thay đổi mới</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('backend.history.restore', $history->id) }}" 
                    class="btn btn-warning text-white" 
                    onclick="return confirm('Bạn có chắc chắn muốn khôi phục lại dữ liệu của lần thay đổi này không?');">
                        <i class="fas fa-undo"></i> Khôi phục dữ liệu cũ
                    </a>
                    <a href="{{ route('backend.history.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jsdiff/5.1.0/diff.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const oldBoxes = document.querySelectorAll('.diff-old-box');
    
    oldBoxes.forEach(oldBox => {
        let key = oldBox.getAttribute('data-key');
        let newBox = document.querySelector(`.diff-new-box[data-key="${key}"]`);
        
        if (newBox) {
            let oldStr = oldBox.textContent;
            let newStr = newBox.textContent;
            
            let diff = Diff.diffWords(oldStr, newStr);
            let oldHtml = '';
            let newHtml = '';
            
            diff.forEach(part => {
                let escapedValue = escapeHtml(part.value);
                if (part.added) {
                    newHtml += `<span style="background-color: #d4edda; color: #155724; font-weight: 600; padding: 1px 2px;">${escapedValue}</span>`;
                } else if (part.removed) {
                    oldHtml += `<span style="background-color: #ffcccc; color: #990000; font-weight: 600; padding: 1px 2px;">${escapedValue}</span>`;
                } else {
                    oldHtml += escapedValue;
                    newHtml += escapedValue;
                }
            });
            
            oldBox.innerHTML = oldHtml;
            newBox.innerHTML = newHtml;
        }
    });
});

function escapeHtml(text) {
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
</x-app-layout>