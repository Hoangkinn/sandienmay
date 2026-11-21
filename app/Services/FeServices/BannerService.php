<?php
namespace App\Services\FeServices;

use App\DTOs\BannerDto;
use App\Enums\Status;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class BannerService extends BaseService
{ 
    public function __construct()
    {
        $this->selectList = ['image', 'image_mobile', 'title', 'link', 'position'];

        parent::__construct(resolve(Banner::class));
    }
    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList($builder)->where('status', Status::ACTIVED);
    }
    public function all(): array
    {
        $keyCache = "allBanner";
        // return Cache::remember($keyCache, 600,  function () {
            $banners = $this->getList()->get();
            return BannerDto::from($banners);
        // });
    }

    public function bannerByPosition(int $position): array
    {
        $keyCache = "banner.$position";
        // return Cache::remember($keyCache, 600,  function () use ($position) {
            $banners = $this->getList()
                ->where('position', $position)
                ->orderBy('order', 'asc')      // Sắp xếp theo số thứ tự tăng dần
                ->orderByDesc('created_at')    // Dự phòng nếu trùng order thì lấy mới nhất
                ->get();
            return BannerDto::from($banners);
        // });
    }
}
