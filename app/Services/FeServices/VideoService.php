<?php

namespace App\Services\FeServices;

use App\DTOs\Category\CategoryListDto;
use App\DTOs\VideoDto;
use App\Enums\CategoryType;
use App\Enums\Status;
use App\Models\Category;
use App\Models\New_Product;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VideoService extends BaseService
{
    public function __construct()
    {
        $cateModel = resolve(Video::class);
        $this->selectList = [
            $cateModel->getTable() . '.id',
            'title',
            'slug',
            'url',
            'image',
            'content',
            'order',
            'status',
            'is_feature',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'seo_url_canonical',
            'seo_no_index',
        ];
        parent::__construct($cateModel);
    }

    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList($builder)->where('status', Status::ACTIVED)
            ->orderBy('order');
    }

     public function getVideos(int $limit = 5)
    {
        $keyCache = "newVideo$limit";
        return Cache::remember($keyCache, 600,  function () use ($limit) {
           $videos = $this->getList()->limit($limit)->get();
           return VideoDto::from($videos);
        });
    }
    
}
