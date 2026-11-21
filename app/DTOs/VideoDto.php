<?php

namespace App\DTOs;

use App\Models\Video;

class VideoDto extends BaseDto
{
    public readonly string $image;
    public readonly string $url;
    public readonly string $title;
    public readonly string $slug;
    public readonly string $youtubeId;
    public function __construct(Video $video)
    {
        $this->image = $video->imageYoutube;
        $this->url = $video->url ?? '';
        $this->title = $video->title;
        $this->slug = $video->slug;
        $this->youtubeId = $video->youtubeId;
    }
}
