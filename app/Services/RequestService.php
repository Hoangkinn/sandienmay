<?php

namespace App\Services;

use Illuminate\Http\Request;

class RequestService
{
    public Request $request;
    public function __construct(Request $request)
    {
        $this->request = $request->replace(array_filter(
            $request->all(),
            fn($v) => $v !== null && $v !== '' && (!is_array($v) || !empty($v))
        ));
    }
}
