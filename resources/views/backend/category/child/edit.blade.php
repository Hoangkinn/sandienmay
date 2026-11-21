@foreach ($childs as $idx => $child)
<option value="{{ $child->id }}" @selected($categoryId == $child->id)>
    {{ $expandable }} {{ $child->name }}
</option>
    @if(count($child->childs))
        @include('backend.category.child.add', [
            'childs' => $child->childs,
            'expandable' => '--' . $expandable
        ])
    @endif
@endforeach