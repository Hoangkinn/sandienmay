@foreach ($childs as $child)
    <option value="{{ $child->id }}" {{ $child->selectMultiple(old('category_id', $category_ids)) }}>
        {{ $expandable }} {{ $child->name }}
    </option>
    @if(count($child->childs))
        @include('backend.category.child.select-multiple', [
            'childs' => $child->childs,
            'expandable' => '--',
            'category_ids' => $category_ids,
        ])
    @endif
@endforeach