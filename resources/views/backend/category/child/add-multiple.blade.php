@foreach ($childs as $child)
<li class="product_category_item">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" name="category_ids[]" id="category-{{ $child->id }}" value="{{ $child->id }}" class="custom-control-input" {{ $child->inCategoryIds(old('category_ids')) }}>
        <label for="category-{{ $child->id }}" class="custom-control-label form-check-label product_category_label">
            {{ $child->name }}
        </label>
        @if(count($child->childs))
        <span class="product_category_wrap_item_span">
            <i class="fas fa-plus"></i>
            <i class="fas fa-minus"></i>
        </span>
        @endif
    </div>
    @if(count($child->childs))
        <ul class="wrapper-children">
        @include('backend.category.child.add-multiple', [
            'childs' => $child->childs
        ])
        </ul>
    @endif
</li>
@endforeach