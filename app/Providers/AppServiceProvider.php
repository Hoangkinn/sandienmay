<?php

namespace App\Providers;

use App\Services\FeServices\CategoryService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Services\FeServices\SettingFieldService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Nhúng thẳng nội dung file CSS đã minify vào HTML thay vì <link>.
     * Ba file CSS chặn render của frontend (~84KB) là nguyên nhân FCP 3,0s trên
     * PageSpeed mobile; inline xoá hẳn số request chặn hiển thị ở head.
     */
    public static function inlineCss(string $path): string
    {
        $file = public_path($path);

        return is_file($file)
            ? '<style>' . file_get_contents($file) . '</style>'
            : '<link rel="stylesheet" href="' . asset($path) . '">';
    }

    /**
     * Xuất width/height đúng bằng ảnh thật trên disk.
     *
     * Khai sai tỉ lệ làm browser chôn ô sai chiều cao rồi nhảy sang chiều cao thật
     * khi ảnh decode xong - đây là nguồn CLS lớn nhất ở trang chủ (banner hero khai
     * 1920x500 trong khi ảnh thật 1440x529, flash-sale khai 1216x447 còn ảnh 1216x204).
     * Không đọc được ảnh thì mới dùng số fallback.
     */
    public static function imgAttrs(?string $url, int $fallbackW, int $fallbackH): string
    {
        static $sizes = [];

        $path = $url ? (parse_url($url, PHP_URL_PATH) ?: '') : '';
        if ($path === '') {
            return 'width="' . $fallbackW . '" height="' . $fallbackH . '"';
        }

        if (!isset($sizes[$path])) {
            $file = public_path(ltrim($path, '/'));
            $real = is_file($file) ? @getimagesize($file) : false;
            $sizes[$path] = $real ? [(int) $real[0], (int) $real[1]] : null;
        }

        [$w, $h] = $sizes[$path] ?? [$fallbackW, $fallbackH];

        return 'width="' . $w . '" height="' . $h . '"';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive(
            'inlineCss',
            fn ($expression) => '<?php echo \App\Providers\AppServiceProvider::inlineCss(' . $expression . '); ?>'
        );

        Blade::directive(
            'imgAttrs',
            fn ($expression) => '<?php echo \App\Providers\AppServiceProvider::imgAttrs(' . $expression . '); ?>'
        );

        View::composer(['web.*', 'frontend.*', 'layouts.frontend', 'web.layout'], function ($view) {
            $settings = [
                'slogan'            => SettingFieldService::getKey('slogan'),
                'customize_header'  => SettingFieldService::getKey('customize_header'),
                'logo'              => asset('storage/' . SettingFieldService::getKey('logo')),
                'favicon'           => asset('storage/' . SettingFieldService::getKey('favicon')),
                'footer_1'          => SettingFieldService::getKey('footer_1'),
                'footer_2'          => SettingFieldService::getKey('footer_2'),
                'footer_3'          => SettingFieldService::getKey('footer_3'),
                'ho_tro_khach_hang' => SettingFieldService::getKey('ho_tro_khach_hang'),
                'sieu_thi_may_rua_xe'=> SettingFieldService::getKey('sieu_thi_may_rua_xe'),
                'danh_muc_san_pham' => SettingFieldService::getKey('danh_muc_san_pham'),
                'ket_noi_voi_chung_toi' => SettingFieldService::getKey('ket_noi_voi_chung_toi'),
                'addressHn'         => SettingFieldService::getKey('address_hn'),
                'phoneHn'           => SettingFieldService::getKey('new_phone_hn'),
                'phoneHn2'          => SettingFieldService::getKey('new_phone_hn2'),
                'addressHcm'        => SettingFieldService::getKey('address_hcm'),
                'phoneHcm'          => SettingFieldService::getKey('new_phone_hcm'),
                'phoneHcm2'         => SettingFieldService::getKey('new_phone_hcm2'),
                'phoneQuickSp'      => SettingFieldService::getKey('quick_support'),
                'mail'              => SettingFieldService::getKey('new_mail'),
                'lien_he_mobile'    => SettingFieldService::getKey('lien_he_mobile'),
                'linkFb'            => SettingFieldService::getKey('link-fb'),
                'linkZalo'          => SettingFieldService::getKey('link-zalo'),
                'linkYoutube'       => SettingFieldService::getKey('link-youtube'),
                'linkTiktok'        => SettingFieldService::getKey('link-tiktok'),
                'giay_chung_nhan'   => SettingFieldService::getKey('giay_chung_nhan'),
                'linkBoCongThuong'  => SettingFieldService::getKey('link-bo-cong-thuong'),
                'ho_tro_online'     => SettingFieldService::getKey('ho_tro_online'),
                'google_map'        => SettingFieldService::getKey('google_map'),
                'google_map_hcm'    => SettingFieldService::getKey('google_map_hcm'),
                'link_mess'         => SettingFieldService::getKey('link_mess'),
                'zalo_hcm'          => SettingFieldService::getKey('zalo_hcm'),
                'zalo_hcm2'         => SettingFieldService::getKey('zalo_hcm2'),
                'zalo_hn'           => SettingFieldService::getKey('zalo_hn'),
                'zalo_hn2'          => SettingFieldService::getKey('zalo_hn2'),
                'tru_so_dkkd'       => SettingFieldService::getKey('tru_so_dkkd'),
                'ma_so_thue'        => SettingFieldService::getKey('ma_so_thue'),
                'postAuthorName'    => SettingFieldService::getKey('post_author_name'),
                'postAuthorRole'    => SettingFieldService::getKey('post_author_role'),
                'postAuthorBio'     => SettingFieldService::getKey('post_author_bio'),
            ];

            $view->with($settings);
        });
    }
}
