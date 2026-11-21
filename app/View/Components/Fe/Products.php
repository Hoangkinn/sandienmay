<?php

namespace App\View\Components\Fe;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Products extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $products, 
        public bool $preload = false, 
        public bool $viewCart = false,
        public bool $viewSoldQuantity = false,
        public bool $quickView = true)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (empty($this->products)) return '';
        return view('components/fe/products');
    }
}
