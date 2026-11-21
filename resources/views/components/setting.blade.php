@if($field['type'] == App\Enums\FieldType::IMAGE->value)
    @if ($field['key'] == 'favicon')
        <link rel="shortcut icon" href="{{asset('storage/' . $field['value'] )}}" type="image/x-icon">
    @else
        <img src="{{asset('storage/' . $field['value'] )}}" alt="{{ $field['name'] }}" title="{{ $field['name'] }}" class="{{ $className }}" />
    @endif
@else
    {!! $field['value'] !!}
@endif
