<?php

namespace App\Http\Controllers\Frontend;

use App\Services\FeServices\CategoryService;
use App\Services\FeServices\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class New_AjaxController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function search(Request $request): JsonResponse
    {
        $s = $request->s;
        if ($s) {
            $products = resolve(ProductService::class)->search($s, 15);
            $categories = resolve(CategoryService::class)->search($s, 10);
        }
        return $this->returnJson(['html' => Blade::render('<x-fe.product-list :products="$products" :categories="$categories" type="search"/>', [
            'products' => $products ?? [],
            'categories' => $categories ?? []
        ])]);
    }
}
