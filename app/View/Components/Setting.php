<?php

namespace App\View\Components;

use App\Services\FeServices\SettingFieldService;
use Illuminate\View\Component;

class Setting extends Component
{
    public ?string $className;

    public array $field;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $fieldKey, $className = null)
    {
        $this->field = SettingFieldService::getKeyOld($fieldKey);
        $this->className = $className;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (!$this->field) {
            return '';
        }
        return view('components/setting');
    }
}
