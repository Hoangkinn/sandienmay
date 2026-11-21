<?php

namespace App\DTOs\Category;

use App\Models\Category;

class CategoryListDto extends BaseCategoryDto
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }
}
