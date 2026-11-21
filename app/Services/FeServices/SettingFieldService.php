<?php
namespace App\Services\FeServices;

use App\DTOs\SettingFieldDto;
use App\Models\SettingField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;

class SettingFieldService extends BaseService
{
    public static array $keyGetForApp = [
        "slogan",
        "logo",
        "hotline",
        "ho_tro_khach_hang",
        "sieu_thi_may_rua_xe",
        "danh_muc_san_pham",
        "ket_noi_voi_chung_toi",
        "thong_tin_ve_cong_ty",
        "footer_1",
        "footer_2",
        "footer_3",
        "giay_chung_nhan",
        "product_contact",
        "favicon",
        "ho_tro_online",
        "customize_footer",
        "customize_header",
        "lien_he_mobile",
        'address_hn',
        'new_phone_hn',
        'new_phone_hn2',
        'address_hcm',
        'new_phone_hcm',
        'new_phone_hcm2',
        'new_mail',
        'link-tiktok',
        'link-youtube',
        'link-zalo',
        'link-fb',
        'link-bo-cong-thuong',
        'quick_support',
        'site_name',
        'site_title',
        'site_keywords',
        'site_description',
        'google_map',
        'google_map_hcm',
        'link_mess',
        'zalo_hcm',
        'zalo_hcm2',
        'zalo_hn',
        'zalo_hn2',
        'tru_so_dkkd',
        'ma_so_thue',
        'post_author_name',
        'post_author_role',
        'post_author_bio'
    ];
    public function __construct()
    {
        $this->selectList = ['field_name', 'field_key', 'field_type', 'field_value'];

        parent::__construct(resolve(SettingField::class));
    }
    public function getList(null|Builder|Relation $builder = null): Relation|Builder
    {
        return parent::getList()->whereIn('field_key', static::$keyGetForApp)
            ->where('field_value', '!=', null)
            ->where('field_value', '!=', '');
    }
    private function all(): array
    {
        $keyCache = "allSettingFields";
        return Cache::remember($keyCache, 600, function (){
            $settings = $this->getList()->get();
            $settings = SettingFieldDto::from($settings);
            $tmp = [];
            foreach ($settings as $setting) {
                $key = $setting['key'];
                $tmp[$key] = $setting;
            }
            return $tmp;
        });
    }

    public static function getKey(string $key): string
    {
        if (!in_array($key, static::$keyGetForApp)) {
            throw new \Exception("$key chưa được khai báo trong Service");
        }
        
        $allSettings = resolve(static::class)->all();
        $value = $allSettings[$key]['value'] ?? '';
        
        // Truyền cả mảng dữ liệu gốc vào để replace trực tiếp, không gọi đệ quy getKey() nữa
        return static::replaceKey($value, $allSettings);
    }

    private static function replaceKey(string $value, array $allSettings): string
    {
        preg_match_all('#\[([a-z0-9-_]{4,})\]#', $value, $keys);
        if ($keys[1]){
            foreach ($keys[1] as $k => $key){
                if (isset($allSettings[$key]['value'])) {
                    // Đệ quy xử lý chuỗi bên trong trước khi thay thế (Hoàn toàn an toàn vì $allSettings là mảng tĩnh có sẵn)
                    $replacedValue = static::replaceKey($allSettings[$key]['value'], $allSettings);
                    $value = str_replace($keys[0][$k], $replacedValue, $value);
                }
            }
        }
        return $value;
    }
    public static function getKeyOld(string $key): array
    {
        if (!in_array($key, static::$keyGetForApp)) {
            throw new \Exception("$key chưa được khai báo trong Service");
        }
        return resolve(static::class)->all()[$key] ?? [];
    }

    public static function canDelete(int $id): bool
{
    $setting = SettingField::find($id); 
    if (!$setting) {
        return false;
    }

    $fieldKey = $setting->field_key;

    // 1. Kiểm tra xem có file Blade nào đang gọi trực tiếp key này không
    if (static::isCalledInBlade($fieldKey)) {
        return false; // Đang được file Blade gọi -> KHÔNG CHO XÓA
    }

    // 2. Kiểm tra xem có bị bản ghi khác trong database gọi đến dạng [field_key] không
    $otherSettings = SettingField::where('id', '!=', $id)->pluck('field_value')->toArray();
    foreach ($otherSettings as $value) {
        if (!empty($value)) {
            $pattern = '#\[' . preg_quote($fieldKey, '#') . '\]#';
            if (preg_match($pattern, $value)) {
                return false; // Đang được cấu hình khác gọi tới -> KHÔNG CHO XÓA
            }
        }
    }

    return true; // Không ai gọi cả -> ĐƯỢC PHÉP XÓA
}

    // Hàm phụ trợ để quét các file Blade
    private static function isCalledInBlade(string $fieldKey): bool
    {
        $viewPath = resource_path('views');
        if (!is_dir($viewPath)) {
            return false;
        }

        // Đệ quy quét tất cả file trong thư mục views
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewPath));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = file_get_contents($file->getPathname());
                
                // Kiểm tra xem file blade có chứa từ khóa key này không 
                // (Ví dụ kiểm tra dạng gọi biến hoặc gọi hàm lấy key)
                if (str_contains($content, $fieldKey)) {
                    return true; 
                }
            }
        }

        return false;
    }
}
