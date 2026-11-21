<?php

namespace App\Pipes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FilterSearch
{
    public function __construct(
        protected ?string $keyword = '',
        protected string|array $column = 'name'
    ) {}

    public function __invoke(Builder $query): Builder
    {
        $keyword = trim($this->keyword);
        if ($keyword === '') {
            return $query;
        }

        $columns = is_array($this->column) ? $this->column : [$this->column];

        return $query->where(function ($q) use ($columns, $keyword) {
            foreach ($columns as $col) {
                if ($this->hasFulltextIndex($q, $col)) {
                    // Ưu tiên MATCH ... AGAINST nếu có index
                    $q->orWhereRaw("MATCH(`$col`) AGAINST(? IN BOOLEAN MODE)", [$keyword]);
                } else {
                    // Fallback nhẹ, tận dụng index nếu có
                    $q->orWhere($col, 'like', '%' . $keyword . '%');
                }
            }
        });
    }

    protected function hasFulltextIndex(Builder $query, string $col): bool
    {
        static $cache = [];

        $table = $query->getModel()->getTable();
        $key = $table . ':' . $col;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        try {
            $indexes = DB::select("
                SHOW INDEX FROM `$table`
                WHERE Table = ? AND Index_type = 'FULLTEXT'
            ", [$table]);

            foreach ($indexes as $index) {
                if ($index->Column_name === $col) {
                    return $cache[$key] = true;
                }
            }
        } catch (\Throwable) {
            // nếu schema lỗi hoặc không quyền xem index thì mặc định false
        }

        return $cache[$key] = false;
    }
}
