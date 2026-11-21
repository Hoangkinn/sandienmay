@foreach ($childs as $child)
<tr>
    <td> {{ $expandable }} {{ $child->name }}</td>
    <td>{{ count($child->products) }}</td>
    <td><input type="checkbox" disabled="" @checked(old('is_home_page', $child->is_home_page))></td>
    <td class="project-state">
        @if ($child->status == App\Enums\Status::ACTIVED->value)
        <span class="badge badge-success">{{ __('Hiển thị') }}</span>
        @else
        <span class="badge badge-danger">{{ __('Ẩn') }}</span>
        @endif
    </td>
    <td class="project-actions text-right">
        <a target="_blank" class="btn btn-primary btn-sm" href="{{ route('frontend.slug', ['slug' => $child->slug]) }}">
            <i class="fas fa-eye"></i>
            {{ __('Xem trên web') }}
        </a>
        <a class="btn btn-info btn-sm" href="{{ route('backend.category.edit', ['type' => $type, 'id' => $child->id]) }}">
            <i class="fas fa-pencil-alt">
            </i>
            {{ __('Sửa') }}
        </a>
        <a class="btn btn-danger btn-sm" onclick="return confirm('Bạn muốn xóa danh mục này?')" href="{{ route('backend.category.delete', ['type' => $type, 'id' => $child->id]) }}">
            <i class="fas fa-trash">
            </i>
            {{ __('Xóa') }}
        </a>
    </td>
</tr>

@if(count($child->childs))
@include('backend.category.child.index', [
'childs' => $child->childs,
'expandable' => '--' . $expandable
])
@endif
@endforeach