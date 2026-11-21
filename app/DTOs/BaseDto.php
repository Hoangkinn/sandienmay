<?php

namespace App\DTOs;

use App\DTOs\DtoSeoTrait;
use App\Models\BaseModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Str;

abstract class BaseDto
{
   public function __construct(BaseModel $item)
    {
        if (in_array(DtoSeoTrait::class, class_uses($this))) {
            $this->dtoSeo($item);
        }
    }
    public static function from(LengthAwarePaginator|DbCollection|Collection|BaseModel|array|null $model): array
    {
        if (! $model) return [];
        if ($model instanceof BaseModel) {
            return (new static($model))->toArray();
        }
        return $model->map(fn ($i) => (new static($i))->toArray())->toArray();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    protected function formatContentBeforeShow(?string $content): string
    {
        if (! $content) return '';
        return str_replace(['<table', '</table>'], ['<div class="overflow-auto"><table', '</table></div>'], $content);
    }
    protected function formatBannerBeforeShow(?string $banner): string
    {
        return str_replace('<img', '<img class="d-block rounded my-2 my-lg-4"', $banner);
    }
    protected function addToc(?string &$content): array
    {
        if (!$content) return [];

        $toc = [];

        $content = preg_replace_callback('#<h([234])[^>]*>(.*?)</h\1>#', function ($m) use (&$toc) {
            $level = (int) $m[1];
            $title = trim(strip_tags($m[2]));
            $id = 'toc-' . Str::slug($title);

            if ($level === 2) {
                // Cấp 1
                $toc[] = [
                    'id' => $id,
                    'title' => $title,
                    'children' => []
                ];
            } elseif ($level === 3) {
                // Cấp 2
                if (!empty($toc)) {
                    $tocIndex = count($toc) - 1;
                    $toc[$tocIndex]['children'][] = [
                        'id' => $id,
                        'title' => $title,
                        'children' => []
                    ];
                }
            } else {
                // Cấp 3 (h4)
                if (!empty($toc)) {
                    $tocIndex = count($toc) - 1;
                    if (!empty($toc[$tocIndex]['children'])) {
                        $subIndex = count($toc[$tocIndex]['children']) - 1;
                        $toc[$tocIndex]['children'][$subIndex]['children'][] = [
                            'id' => $id,
                            'title' => $title
                        ];
                    }
                }
            }

            // Thêm id vào heading
            return preg_replace(
                '#<h([234])([^>]*)>#',
                "<h$1 id=\"$id\"$2>",
                $m[0],
                1
            );
        }, $content);

        return $toc;
    }
}
