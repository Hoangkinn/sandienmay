<?php

namespace App\Traits;

use App\DTOs\Category\CategoryListDto;
use App\Enums\CategoryType;
use App\Enums\Status;
use App\Models\Category;

trait CategoryTrait
{
    public function getCategories($isAll = false)
    {
        $query = Category::orderBy('order')
            ->where('type', CategoryType::PRODUCT->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', null);
            
        if (!$isAll) {
            $query->limit(12);
        }

        return $query->get();
    }

    public function getCategoriesPost()
    {
        $query = Category::orderBy('order')
            ->where('type', CategoryType::POST->value)
            ->where('status', Status::ACTIVED->value)
            ->where('parent_id', '>', 0);

        return CategoryListDto::from($query->get());
    }
}