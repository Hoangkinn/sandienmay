<?php

namespace App\View\Components\Fe;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductList extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public array $products,
                                public string $type = '',
                                public array $categories)
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (empty($this->products) && empty($this->categories) )
            return '';
        return view('components/fe/product-list');
    }
}
