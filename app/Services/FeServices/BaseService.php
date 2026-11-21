<?php
namespace App\Services\FeServices;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

abstract class BaseService
{
    public array $selectList = ['*'];
    public array $selectDetail = ['*'];
    public function __construct(public readonly BaseModel $model)
    {

    }
    public function getList(null|Builder|Relation $builder = null): Builder|Relation
    {
        if ($builder){
            return $builder->select($this->selectList);
        }
        return $this->model->newQuery()->select($this->selectList);
    }
    public function getDetail(null|Builder|Relation $builder = null): Builder|Relation
    {
        if ($builder){
            return $builder->select($this->selectDetail);
        }
        return $this->model->newQuery()->select($this->selectDetail);
    }
    public function getByIds(array $ids, bool $sortById = false): Collection
    {
        $ids = array_filter($ids);
        $query = $this->getList()->whereIn('id', $ids);
        if ($sortById) {
            $query = $query->orderByRaw("FIELD(id, ".implode(',', $ids).")");
        }
        return $query->get();
    }
    public function getBySlug(string $slug, string $columnSlug = 'slug'): ?BaseModel
    {
        // return Cache::rememberForever($this->model->getTable()."-$slug", function () use ($slug, $columnSlug) {
            return $this->getDetail()->where($columnSlug, $slug)->first();
        // });
    }
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
    public function getSitemapIndexData(int $perPage = 500, ?string $type = null): array
    {
        // 1. Lấy tổng số lượng item đủ điều kiện
        $totalItems = $this->model->newQuery()
            ->where('is_sitemap', IsSitemapEnum::Yes);
        if ($type) $totalItems = $totalItems->where('type', $type);

        $totalItems = $totalItems->count();

        // 2. Tính tổng số trang (sitemap con)
        $totalSitemaps = (int) ceil($totalItems / $perPage);

        // 3. Khởi tạo mảng để lưu thông tin của từng sitemap con
        $sitemapData = [];

        // 4. Lặp qua từng "page" (sitemap con) để lấy updated_at mới nhất cho mỗi page
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            // Lấy updated_at gần nhất của các item trên trang hiện tại
            // Sắp xếp theo updated_at DESC, áp dụng offset, và chỉ lấy 1 bản ghi
            $latestUpdatedAtForPage = $this->model->newQuery()
                ->select('updated_at')
                ->where('is_sitemap', IsSitemapEnum::Yes);
            if ($type)
                $latestUpdatedAtForPage = $latestUpdatedAtForPage->where('type', $type);

            $latestUpdatedAtForPage = $latestUpdatedAtForPage->orderBy('updated_at', 'desc') // Sắp xếp giảm dần theo thời gian cập nhật
                ->offset(($page - 1) * $perPage) // Bắt đầu từ bản ghi đầu tiên của "trang" này
                ->limit(1) // Chỉ lấy 1 bản ghi duy nhất (là bản ghi mới nhất của "trang" đó)
                ->first();

            // Thời gian cập nhật cho sitemap con hiện tại
            // Nếu không tìm thấy bản ghi nào (ví dụ: totalItems = 0), dùng thời gian hiện tại
            $lastmod = $latestUpdatedAtForPage ? $latestUpdatedAtForPage->updated_at : Carbon::now();

            // Thêm thông tin sitemap con vào mảng
            // Giả định bạn có một route cho sitemap con, ví dụ: sitemap-posts-1.xml, sitemap-posts-2.xml
            $sitemapData[] = [
                'loc' => $page, // URL của sitemap con
                'lastmod' => $lastmod,
            ];
        }

        return $sitemapData;
    }
    /*public function search(?string $keyword, int $limit, string $classDTO, string|array $columns = 'name'): array
    {
        if (is_string($columns)) $columns = [$columns];

        $items = $this->getList()->where(function ($q) use ($columns, $keyword) {
            foreach ($columns as $col) {
                $q->orWhereRaw("REPLACE(REPLACE($col, ' ', ''), '-', '') LIKE ?", [
                    '%' . str_replace([' ', '-'], '', $keyword) . '%'
                ]);
            }
        })->paginate($limit)->withQueryString();
        return [
            'items' => $classDTO::from($items),
            'loadmore' => $items->nextPageUrl(),
            'total' => $items->total(),
        ];
    }*/
    public function getModel(array $data): BaseModel
    {
        return $this->model->newInstance($data, true);
    }
}
