<?php

namespace App\View\Components\Fe;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toc extends Component
{
    /**
     * Create a new component instance.
     */
    public string $currentUrl;
    public function __construct(public array $toc)
    {
        $this->currentUrl = url()->current();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.fe.toc');
    }
}
