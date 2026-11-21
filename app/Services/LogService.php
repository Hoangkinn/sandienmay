<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogService
{
    public static function backend()
    {
        return Log::stack(['backend']);
    }
    public static function frontend(){
        return Log::stack(['frontend']);
    }
    public static function log404()
    {
        return Log::stack(['log404']);
    }
    public static function hack()
    {
        return Log::stack(['hack']);
    }
}
