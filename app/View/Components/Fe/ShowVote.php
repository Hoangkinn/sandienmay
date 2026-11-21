<?php

namespace App\View\Components\Fe;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShowVote extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public int|float|null $avg = null)
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components/fe/show-vote');
    }
}