<?php
namespace App\Services\FeServices;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class SettingService extends BaseService
{
    public function __construct()
    {
        $this->selectList = ['setting_name', 'setting_value'];

        parent::__construct(resolve(Setting::class));
    }
    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList()->whereRaw('setting_value is not null and setting_value != ""');
    }
    public function all(): array
    {
        $keyCache = "allSettings";
        return Cache::remember($keyCache, 600, function (){
            $settings = $this->getList()->get();
            return $settings->pluck('setting_value', 'setting_name')->toArray();
        });
    }
}
