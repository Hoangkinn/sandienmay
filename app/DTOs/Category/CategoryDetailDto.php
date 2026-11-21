<?php

namespace App\DTOs\Category;

use App\Models\Category;

class CategoryDetailDto extends BaseCategoryDto
{
    public ?string $content;
    public array $toc = [];
    public readonly string $seoTitle;
    public readonly ?string $seoDescription;
    public readonly ?string $seoUrlCanonical;
    public readonly string $robots;
    public function __construct(Category $category)
    {
        parent::__construct($category);
        $content = $category->excerpt;
        $toc = $this->addToc($content);
        $this->content = $content;
        $this->toc = $toc;
        $this->seoTitle = $category->seo_title ?? '';
        $this->seoDescription = $category->seo_description ?? '';
        $this->seoUrlCanonical = $category->seo_url_canonical;
        $this->robots = !$category->seo_no_index ? 'index,follow' : 'noindex,nofollow';
    }
}
