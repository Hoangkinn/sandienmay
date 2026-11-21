<?php
namespace App\Services\FeServices;

use App\DTOs\ReviewDto;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class ReviewService extends BaseService
{
    public function __construct()
    {
        $this->selectList = ['avatar', 'name', 'content'];

        parent::__construct(resolve(Review::class));
    }
    
    public function all(): array
    {
        $keyCache = "allReviews";
        return Cache::remember($keyCache, 600,  function () {
            $banners = $this->getList()->get();
            return ReviewDto::from($banners);
        });
    }
}
