<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    protected Request $request;
    public function __construct(){
        Paginator::useBootstrap();
        request()->replace(array_filter(request()->all(), fn($v) => $v !== null && $v !== '' && (!is_array($v) || $v)));
        $this->request = \request();
    }
    public function view(string $path, array $data): View
    {
        return view("web.$path", $data);
    }
    public function redirect(string $routeName): RedirectResponse
    {
        return redirect()->route($routeName);
    }
    public function returnJson(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }
}
