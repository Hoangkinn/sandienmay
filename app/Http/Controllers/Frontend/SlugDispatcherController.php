<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\CategoryType;
use App\Enums\PostType;
use App\Models\Post;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\ProductService;

class SlugDispatcherController
{
    public function handle(string $slug)
    {
        if ($slug === 'san-pham-da-xem') {
            return resolve(New_ProductController::class)->viewAllRecent();
        }

        if ($slug === 'san-pham-lien-quan') { 
            return resolve(New_ProductController::class)->viewAllRelated();
        }

        
        if ($category = resolve(CategoryService::class)->getBySlug($slug)) {
            if ($category->type == CategoryType::POST->value)
                return resolve(CategoryController::class)->slug($category);
            return resolve(New_ProductController::class)->category($category);
        }

        if ($product = resolve(ProductService::class)->getBySlug($slug)) {
            return app(New_ProductController::class)->product($product);
        }
        if (Post::where('slug', $slug)->where('type', PostType::PAGE->value)->first()) {
            return app(PageController::class)->slug($slug);
        }
        if (Post::where('slug', $slug)->where('type', PostType::POST->value)->first()) {
            return app(PostController::class)->slug($slug);
        }

        abort(404);
    }
}
