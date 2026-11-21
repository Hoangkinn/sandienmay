@foreach ($childs as $child)
<option value="{{ $child->id }}" {{ $child->isSelected($child->id, $promotion_id) }}>
    {{ $expandable }} {{ $child->name }}
</option>
    @if(count($child->childs))
        @include('backend.category.child.promotion', [
            'childs' => $child->childs,
            'promotion_id' => $promotion_id,
            'expandable' => '--' . $expandable
        ])
    @endif
@endforeach