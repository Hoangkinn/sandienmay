<?php

namespace App\Http\Controllers\Frontend;

use App\DTOs\Product\ProductListDto;
use App\Http\Controllers\Frontend\BaseController;
use App\Pipes\FilterSearch;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\ProductService;
use Illuminate\Http\JsonResponse;

class AjaxController extends BaseController
{
    public function handleGet(string $any): JsonResponse
    {
        if (!method_exists($this, $any)) {
            abort(404);
        }
        try {
            $data = $this->{$any}();
        } catch (\Throwable $throwable) {
            return response()->json([
                'message' => $throwable->getMessage()
            ], 422);
        }
        return response()->json($data);
    }

    private function showModalCompare()
    {
        $ids = $this->request->input();
        if ($ids) {
            $products = resolve(ProductService::class)->getByIds($ids, true);
            $products = ProductListDto::from($products);
        } else {
            $products = [];
        }

        return [
            'html' => $this->view('ajax/modal-compare', compact('products'))->render(),
        ];
    }

    // private function showModalCompareSelectProduct()
    // {
    //     $id = $this->request->get('id');
    //     $keyword = $this->request->get('keyword');
    //     $products = resolve(ProductService::class)->getByIds([$id]);
    //     if ($products->isNotEmpty()) {
    //         $product = $products[0];
    //         $categories = resolve(CategoryService::class)->getCategoryByProduct($product, false);
    //         $q = resolve(ProductService::class)->getByCategories($categories->pluck('id')->toArray());
    //     } else {

    //         $q = resolve(ProductService::class)->getList();
    //     }
    //     $products = $q->pipe(new FilterSearch($keyword))
    //         ->get();
    //     $products = ProductListDto::from($products);


    //     return [
    //         'html' => $this->view('ajax/modal-compare-select-product', compact('products', 'keyword'))->render(),
    //     ];
    // }
    private function showModalCompareSelectProduct()
    {
        $id = $this->request->get('id');
        $keyword = trim($this->request->get('keyword')); // Cắt bỏ khoảng trắng thừa đầu/cuối
        
        $products = resolve(ProductService::class)->getByIds([$id]);
        if ($products->isNotEmpty()) {
            $product = $products[0];
            $categories = resolve(CategoryService::class)->getCategoryByProduct($product, false);
            $q = resolve(ProductService::class)->getByCategories($categories->pluck('id')->toArray());
        } else {
            $q = resolve(ProductService::class)->getList();
        }

        // Xử lý keyword để tìm kiếm linh hoạt hơn (ví dụ: "kms 20" -> "kms%20" hoặc xóa khoảng trắng)
        if (!empty($keyword)) {
            // Cách 1: Thay thế khoảng trắng bằng dấu % để khớp với các ký tự ở giữa (như dấu gạch ngang)
            $searchKeyword = str_replace(' ', '%', $keyword);
            
            // Hoặc Cách 2: Xóa hoàn toàn khoảng trắng nếu muốn "kms 20" thành "kms20"
            // $searchKeyword = str_replace(' ', '', $keyword);
        } else {
            $searchKeyword = $keyword;
        }

        $products = $q->pipe(new FilterSearch($searchKeyword))
            ->get();
        $products = ProductListDto::from($products);

        return [
            'html' => $this->view('ajax/modal-compare-select-product', compact('products', 'keyword'))->render(),
        ];
    }
}
