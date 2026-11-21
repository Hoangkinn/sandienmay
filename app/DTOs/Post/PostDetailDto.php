<?php

namespace App\DTOs\Post;

use App\DTOs\DtoSeoTrait;
use App\Models\Post;

class PostDetailDto extends BasePostDto
{
    use DtoSeoTrait;

    public ?string $content;
    public array $toc = [];
    public readonly string $robots;
    public readonly string $author;
    public readonly string $updated_at;
    public readonly string $created_at;
    public readonly string $schema;
    public function __construct(Post $post)
    {
        parent::__construct($post);

        $this->dtoSeo($post);

        $content = $post->content;
        $toc = $this->addToc($content);
        $this->content = $this->formatContentBeforeShow($content);
        $this->toc = $toc;
        // $this->robots = !$post->seo_no_index ? 'index,follow' : 'noindex,nofollow';
        $this->robots = $this->isIndex ? 'index,follow' : 'noindex,nofollow';
        $this->author = $post->author ? $post->author->name : 'Admin';
        $this->created_at = $post->created_at;
        $this->updated_at = $post->updated_at;
        $this->schema = $post->schema ?? \DB::table('m_posts')->where('id', $post->id)->value('schema') ?? '';
    }
}
