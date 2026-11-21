<?php

namespace App\DTOs;

use App\Enums\IsIndexEnum;
use App\Helpers\GetFeUrlHelper;
use App\Models\BaseModel;

trait DtoSeoTrait
{
    public string $seoTitle;
    public string $seoDescription;
    public bool $isIndex;
    public string $canonical;
    public string $seoKeyword;

    protected function dtoSeo(BaseModel $item): void
    {
        $this->seoTitle = $item->seo_title ?? '';
        $this->seoDescription = $item->seo_description ?? '';
        $this->isIndex = !$item->seo_no_index;
        $this->canonical = $item->seo_url_canonical ?? route('frontend.slug', $item->slug);
        $this->seoKeyword = $item->seo_keywords ?? '';
    }
}
