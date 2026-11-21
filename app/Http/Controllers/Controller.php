<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // Tự động xóa lịch sử cũ hơn 20 ngày, mỗi ngày chỉ chạy 1 lần qua Cache
        Cache::remember('clean_old_histories_daily', now()->addDay(), function () {
            History::where('created_at', '<', Carbon::now()->subDays(20))->delete();
            return true;
        });
    }
}