<?php

namespace App\Http\Controllers;

use App\Traits\CategoryTrait;
use App\Traits\SettingTrait;

class FrontendController extends Controller
{
    use CategoryTrait;
    use SettingTrait;
}
