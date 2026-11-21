<?php
namespace App\Traits;

use App\Models\Setting;

trait SettingTrait
{
    public function getSettings()
    {
        return Setting::all();
    }
}
