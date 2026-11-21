<?php

namespace App\DTOs;

use App\Models\Banner;

class BannerDto extends BaseDto
{
    public readonly string $image;
    public readonly string $imageMobile;
    public readonly string $url;
    public readonly string $name;
    public function __construct(Banner $banner)
    {
        $this->image = $banner->image ? "/storage/$banner->image" : '';
        $this->imageMobile = $banner->image_mobile ? "/storage/$banner->image_mobile" : '';
        $this->url = $banner->link ?? '';
        // $this->name = $banner->title;
        $this->name = $banner->title ?? '';
    }
}
