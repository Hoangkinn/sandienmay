<?php
// Fixture mega menu (LOCAL): php deploy/seed-menu-demo.php
// Chạy lại được (idempotent). Hoàn tác:
//   delete from m_categories where seo_keywords = 'ZZ-MENU-FIXTURE';
//   (kèm 3 danh mục cấp 1 may-cong-nghiep / may-van-phong / dung-cu-dien cũng mang marker này)

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use Illuminate\Support\Str;

$marker = 'ZZ-MENU-FIXTURE';

// Cac nhom dien ra theo mau: brand (logo), attribute (nhu cau), spec (thong so), price (phan khuc gia)
$groups = [
    'brand' => ['Palada', 'Kumisai', 'Camry', 'Supper Clean'],
    'attribute' => ['Dùng cho gia đình', 'Dùng cho xưởng sản xuất', 'Công suất lớn', 'Giá rẻ'],
    'spec' => ['Công suất 1500 - 2000W', 'Dung tích 60 - 100 lít', 'Bình chứa inox', '2 motor'],
    'price' => ['Dưới 3 triệu', '3 - 6 triệu', '6 - 10 triệu', 'Trên 10 triệu'],
];

$parents = [
    // [slug cha da ton tai | null, ten cap 1 moi | null, slug moi | null, anh icon | null, chi tao nhom | null]
    [null, 'Máy công nghiệp', 'may-cong-nghiep', 'mua-may-hut-bui-cong-nghiep-1787713887-1791520860.png', null],
    [null, 'Máy văn phòng', 'may-van-phong', 'may-danh-giay-chinh-hang-1787715168-1791529667.png', null],
    [null, 'Dụng cụ điện', 'dung-cu-dien', 'ruaxe-1784878535-1791529467.png', null],
    ['may-hut-bui-cong-nghiep', null, null, null, ['spec', 'price']],
    ['may-cha-san', null, null, null, null],
    ['xe-quet-rac', null, null, null, null],
    ['may-rua-xe', null, null, null, null],
    ['thap-giai-nhiet', null, null, null, null],
    ['tu-chong-am', null, null, null, null],
    ['may-hut-am', null, null, null, null],
    ['thiet-bi-an-ninh', null, null, null, null],
];

$logos = [
    'brands/1791853905_1787978403_logo-palada-2.png',
    'brands/1791853914_1787976578_logo-kumisai.png',
    'brands/1791853873_1787977236_logo-camry.png',
    'brands/1791853896_1787978916_logo-supperclean.png',
];
$thumbs = ['demo-sp-1.png', 'demo-sp-2.png', 'home-cat.png', 'mhb-1792814862.jpg'];

$makeSlug = function ($name, $parentId) {
    $base = Str::slug($name);
    $slug = "{$base}-{$parentId}";
    $i = 2;
    while (Category::where('slug', $slug)->exists()) {
        $slug = "{$base}-{$parentId}-{$i}";
        $i++;
    }
    return $slug;
};

$created = 0;
foreach ($parents as [$existSlug, $newName, $newSlug, $newImage, $onlyGroups]) {
    if ($existSlug) {
        $parent = Category::where('slug', $existSlug)->first();
        if (!$parent) {
            echo "BO QUA (khong thay cha): $existSlug\n";
            continue;
        }
        if (!$parent->is_menu) {
            $parent->is_menu = 1;
            $parent->save();
        }
    } else {
        $parent = Category::firstOrCreate(
            ['slug' => $newSlug],
            [
                'name' => $newName,
                'image' => $newImage,
                'parent_id' => null,
                'type' => 'product',
                'status' => 'actived',
                'is_menu' => 1,
                'is_home_page' => 0,
                'display_type' => 'attribute',
                'order' => 20,
                'seo_keywords' => $marker,
            ]
        );
        if ($parent->wasRecentlyCreated) {
            $created++;
            echo "CAP 1 moi: {$parent->id} {$parent->name}\n";
        } else {
            echo "CAP 1 da co: {$parent->id} {$parent->name}\n";
        }
    }

    $i = 0;
    $n = 0;
    foreach ($groups as $type => $names) {
        if ($onlyGroups && !in_array($type, $onlyGroups, true)) continue;
        foreach ($names as $k => $name) {
            if (Category::where('parent_id', $parent->id)->where('seo_keywords', $marker)->where('name', $name)->exists()) continue;

            $i++;
            $image = match ($type) {
                'brand' => $logos[$k % count($logos)],
                'attribute' => $thumbs[$k % count($thumbs)],
                default => null,
            };
            $badge = ($type === 'brand' && $k === 0 && $i % 3 === 1) ? 'hot'
                : (($type === 'attribute' && $k === 1 && $i % 2 === 0) ? 'new' : null);

            Category::create([
                'name' => $name,
                'slug' => $makeSlug($name, $parent->id),
                'parent_id' => $parent->id,
                'type' => 'product',
                'status' => 'actived',
                'is_menu' => 1,
                'display_type' => $type,
                'image' => $image,
                'badge' => $badge,
                'order' => $i,
                'seo_keywords' => $marker,
            ]);
            $created++;
            $n++;
        }
    }
    echo "  + {$n} con cho {$parent->name}\n";
}

echo "Tong row tao moi: $created\n";
