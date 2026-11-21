@foreach ($childs as $child)
<li class="dd-item" data-id="{{ $child->id }}" data-children="">
    <div class="dd-handle">
        <span>{{ $child->name }} 
            ({{ $type == App\Enums\CategoryType::PRODUCT->value ? $child->products()->count() : $child->posts()->count() }})
            {{-- ({{ $type == App\Enums\CategoryType::PRODUCT->value ? count($child->products) : count($child->posts) }}) --}}
        </span>
        <div class="float-right">
            
            <span class="dd-nodrag mr-3">
                @if($type == App\Enums\CategoryType::PRODUCT->value)
                    Menu Children
                    <label class="switch">
                        <input type="checkbox" class="toggle-is-menu" 
                            data-id="{{ $child->id }}" 
                            {{ $child->is_menu ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                @endif
            </span>
            {!! $child->getStatusName() !!}
            <a href="{{ route('frontend.slug', ['slug' => $child->slug]) }}" target="_blank" title="Xem trang"
                class="dd-nodrag ml-2">
                Xem trang
            </a>
            <a href="{{ route('backend.category.edit', ['type' => $type, 'id' => $child->id]) }}"
                class="dd-nodrag ml-2">
                Sửa
            </a>
            <a class="text-danger table-btn-delete dd-nodrag ml-2"
                onclick="return confirm('Bạn muốn xóa danh mục này?')"
                href="{{ route('backend.category.delete', ['type' => $type, 'id' => $child->id]) }}">
                Xóa
            </a>
        </div>
    </div>
    @if(count($child->childs))
    <ol class="dd-list">
        @include('backend.category.child.nestable', [
        'childs' => $child->childs,
        'type' => $type
        ])
    </ol>
    @endif
</li>
@endforeach
<script>
    $(document).on('change', '.toggle-is-menu', function() {
    let checkbox = $(this);
    let categoryId = checkbox.data('id');
    let isMenu = checkbox.is(':checked') ? 1 : 0;

    $.post("{{ route('backend.category.toggle-menu') }}", {
        'id': categoryId,
        'is_menu': isMenu,
        '_token': $('meta[name="csrf-token"]').attr('content')
    }, function(response) {
        if (response.success) {
            toastr.success(response.message);
        } else {
            toastr.error('Có lỗi xảy ra!');
            checkbox.prop('checked', !checkbox.is(':checked'));
        }
    });
});
</script>