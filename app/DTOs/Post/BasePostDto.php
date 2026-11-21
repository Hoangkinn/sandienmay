<?php

namespace App\DTOs\Post;

use App\DTOs\BaseDto;
use App\Models\Post;
use Illuminate\Support\Carbon;

abstract class BasePostDto extends BaseDto
{
    public readonly int $id;
    public readonly string $title;
    public readonly string $url;
    public readonly string $image;
    public readonly string $type;
    public readonly int $order;
    public readonly int $count_view;
    public readonly string $seo_title;
    public readonly string $seo_keywords;
    public readonly string $seo_description;
    public readonly string $excerpt;
    public readonly string $date;

    public function __construct(Post $post)
    {

        // dd($post->created_at);   
        parent::__construct($post);
        $this->id = $post->id;
        $this->title = $post->title;
        $this->url = route('frontend.slug', $post->slug);
        $this->image = $post->image ? "/storage/{$post->image}" : '';
        $this->count_view = $post->count_view ?? 0;
        $this->seo_description = $post->seo_description ?? '';
        $this->seo_keywords = $post->seo_keywords ?? '';
        $this->seo_title = $post->seo_title ?? '';
        $this->excerpt = $post->excerpt ?? '';
        $this->date = $post->created_at ?? null;
    }
}
