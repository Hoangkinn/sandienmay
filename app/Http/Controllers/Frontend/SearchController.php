<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SearchController extends FrontendController
{
    public function product(Request $request)
    {
        $query = $request->query('query');
        $products = Product::select(
                'm_products.id',
                'm_products.name as value',
                'm_products.slug as url',
                'm_products.image',
                'm_product_images.url as image2',
                'm_products.regular_price',
                'm_products.sale_price'
            )->leftJoin('m_product_images', function($leftJoin) {
                $leftJoin->on('m_products.id', '=', 'm_product_images.product_id');
                $leftJoin->where('m_product_images.has_primary', '=', 1);
            });

        if ($query) {
            $query = Str::replace(' ', '%', $query);
            $products = $products->where('m_products.name', 'like', '%' . $query . '%')->orWhere(DB::raw("REPLACE(m_products.name, ' ', '')"), 'like', '%' . $query . '%');
        }

        return [
            'suggestions' => $products->get()
        ];
    }
}
