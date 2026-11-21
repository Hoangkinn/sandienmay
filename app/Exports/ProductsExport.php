<?php

namespace App\Exports;

use App\Models\New_Product;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return New_Product::query();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Khuyến mại',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->promotion
        ];
    }
}

