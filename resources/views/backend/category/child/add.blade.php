{{-- @foreach ($childs as $idx => $child)
<option value="{{ $child->id }}")>
    {{ $expandable }} {{ $child->name }}
</option>
    @if(count($child->childs))
        @include('backend.category.child.add', [
            'childs' => $child->childs,
            'expandable' => '--' . $expandable
        ])
    @endif
@endforeach --}}
@foreach($childs as $child)
    {{-- Nếu level là 2 (cấp 2) thì data-level="2", nếu là cấp 3 thì data-level="3" --}}
    <option value="{{ $child->id }}" data-level="{{ $level ?? 2 }}">
        {{ $expandable }} {{ $child->name }}
    </option>
    
    @if(count($child->childs))
        @include('backend.category.child.add', [
            'childs' => $child->childs,
            'expandable' => $expandable . '--',
            'level' => ($level ?? 2) + 1
        ])
    @endif
@endforeach