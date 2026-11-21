<?php
// 1) Xoá 27 fixture cũ (2281-2307).
// 2) Mega menu cho 352,353,354,355,356,362,363: tạo con (brand/attribute), gán sp đúng 1 danh mục,
//    thêm m_category_price_bands + m_category_specs.
// 3) Sửa brand cho sp 355/356 theo tên thật.
// 4) 554/555 bật is_menu; 556/557/558 kéo vào cấp 2 dưới 351 + is_menu.
error_reporting(0);

require 'D:/laragon/www/sandienmay/vendor/autoload.php';
$app = require 'D:/laragon/www/sandienmay/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

$now = now();

// ---------- 1. Xoá fixture cũ ----------
$oldIds = DB::table('m_products')->whereBetween('id', [2281, 2307])->pluck('id');
foreach ([
    'm_product_attributes' => 'product_id',
    'm_product_images' => 'product_id',
    'm_category_product' => 'product_id',
] as $table => $col) {
    DB::table($table)->whereIn($col, $oldIds)->delete();
}
DB::table('m_products')->whereIn('id', $oldIds)->delete();
echo 'deleted old fixtures: ' . count($oldIds) . "\n";

// ---------- Helpers ----------
$brandId = [];
foreach (DB::table('brands')->get(['id', 'name']) as $b) {
    $brandId[mb_strtolower($b->name, 'UTF-8')] = (int) $b->id;
}
$ensureBrand = function (string $name) use (&$brandId, $now) {
    $key = mb_strtolower($name, 'UTF-8');
    if (!isset($brandId[$key])) {
        $brandId[$key] = (int) DB::table('brands')->insertGetId([
            'name' => $name, 'image' => null, 'is_active' => 1, 'order' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "new brand: $name\n";
    }
    return $brandId[$key];
};
$ensureBrand('Dry-Cabi');
$ensureBrand('FujiE');
$ensureBrand('Shiny');
$ensureBrand('Brother Apus');
$ensureBrand('Eureka');
$ensureBrand('Wonderful');

$ensureCat = function (string $name, string $slug, int $parentId, string $displayType) use ($now) {
    $row = DB::table('m_categories')->where('slug', $slug)->first();
    if ($row) {
        DB::table('m_categories')->where('id', $row->id)->update([
            'is_menu' => 1, 'display_type' => $displayType, 'parent_id' => $parentId,
        ]);
        return (int) $row->id;
    }
    return (int) DB::table('m_categories')->insertGetId([
        'name' => $name, 'slug' => $slug, 'parent_id' => $parentId, 'type' => 'product',
        'status' => 'actived', 'is_menu' => 1, 'is_home_page' => 0, 'display_type' => $displayType,
        'seo_title' => $name, 'seo_description' => $name, 'created_at' => $now, 'updated_at' => $now,
    ]);
};

$linkBrand = function (int $catId, string $brandKey) use ($brandId) {
    $bid = $brandId[$brandKey];
    DB::table('category_brand_pivot')->insertOrIgnore([
        'category_id' => $catId, 'brand_id' => $bid, 'order_pivot' => 0,
    ]);
};

// Gán sp của $parentId sang child theo rule đầu tiên khớp; trả số sp đã gán
$assign = function (int $parentId, array $rules) {
    $products = DB::table('m_products as p')
        ->join('m_category_product as cp', 'cp.product_id', '=', 'p.id')
        ->where('cp.category_id', $parentId)
        ->get(['p.id', 'p.name']);
    $moved = 0;
    foreach ($products as $p) {
        $n = mb_strtolower($p->name, 'UTF-8');
        foreach ($rules as $needle => $childId) {
            if ($childId !== null && str_contains($n, $needle)) {
                DB::table('m_category_product')->where('product_id', $p->id)->update(['category_id' => $childId]);
                $moved++;
                break;
            }
        }
    }
    return $moved;
};

// ---------- 2. Tạo con + gán sp ----------

// 352 Xe quét rác
$xeNgoiLai = $ensureCat('Xe quét rác ngồi lái', 'xe-quet-rac-ngoi-lai', 352, 'attribute');
$xeDayTay = $ensureCat('Xe quét rác đẩy tay', 'xe-quet-rac-day-tay', 352, 'attribute');
$xeKumisai = $ensureCat('Xe quét rác Kumisai', 'xe-quet-rac-kumisai', 352, 'brand');
$linkBrand($xeKumisai, 'kumisai');
echo '352 moved: ' . $assign(352, [
    'đẩy tay' => $xeDayTay,
    'ngồi lái' => $xeNgoiLai,
    'kumisai' => $xeKumisai,
]) . "\n";

// 353 Máy rửa xe
$ruaGiaDinh = $ensureCat('Máy rửa xe gia đình', 'may-rua-xe-gia-dinh', 353, 'attribute');
$ruaCaoAp = $ensureCat('Máy rửa xe cao áp', 'may-rua-xe-cao-ap', 353, 'attribute');
$ruaPalada = $ensureCat('Máy rửa xe Palada', 'may-rua-xe-palada', 353, 'brand');
$ruaLutian = $ensureCat('Máy rửa xe Lutian', 'may-rua-xe-lutian', 353, 'brand');
$linkBrand($ruaPalada, 'palada');
$linkBrand($ruaLutian, 'lutian');
echo '353 moved: ' . $assign(353, [
    'palada' => $ruaPalada,
    'lutian' => $ruaLutian,
    'gia đình' => $ruaGiaDinh,
    'cao áp' => $ruaCaoAp,
    'phun rửa' => $ruaCaoAp,
    'tự động ngắt' => $ruaCaoAp,
]) . "\n";

// 354 Tháp giải nhiệt
$thapLiangChi = $ensureCat('Tháp giải nhiệt Liang Chi', 'thap-giai-nhiet-liang-chi', 354, 'brand');
$thapKumisai = $ensureCat('Tháp giải nhiệt Kumisai', 'thap-giai-nhiet-kumisai', 354, 'brand');
$thapTron = $ensureCat('Tháp giải nhiệt tròn', 'thap-giai-nhiet-tron', 354, 'attribute');
$linkBrand($thapLiangChi, 'liang chi');
$linkBrand($thapKumisai, 'kumisai');
echo '354 moved: ' . $assign(354, [
    'liang chi' => $thapLiangChi,
    'kumisai' => $thapKumisai,
    'tròn' => $thapTron,
]) . "\n";

// 355 Tủ chống ẩm
$tuMayAnh = $ensureCat('Tủ chống ẩm máy ảnh', 'tu-chong-am-may-anh', 355, 'attribute');
$tuKumisai = $ensureCat('Tủ chống ẩm Kumisai', 'tu-chong-am-kumisai', 355, 'brand');
$tuDryCabi = $ensureCat('Tủ chống ẩm Dry-Cabi', 'tu-chong-am-dry-cabi', 355, 'brand');
$tuFujie = $ensureCat('Tủ chống ẩm FujiE', 'tu-chong-am-fujie', 355, 'brand');
$linkBrand($tuKumisai, 'kumisai');
$linkBrand($tuDryCabi, 'dry-cabi');
$linkBrand($tuFujie, 'fujie');
echo '355 moved: ' . $assign(355, [
    'máy ảnh' => $tuMayAnh,
    'hộp giữ ẩm' => $tuMayAnh,
    'kumisai' => $tuKumisai,
    'dry-cabi' => $tuDryCabi,
    'fujie' => $tuFujie,
]) . "\n";

// Sửa brand sp 355 theo tên thật
foreach (DB::table('m_products as p')->join('m_category_product as cp', 'cp.product_id', '=', 'p.id')
    ->whereIn('cp.category_id', [$tuMayAnh, $tuKumisai, $tuDryCabi, $tuFujie, 355])
    ->get(['p.id', 'p.name']) as $p) {
    $n = mb_strtolower($p->name, 'UTF-8');
    $bid = $brandId['kumisai'];
    if (str_contains($n, 'dry-cabi')) $bid = $brandId['dry-cabi'];
    elseif (str_contains($n, 'fujie')) $bid = $brandId['fujie'];
    elseif (str_contains($n, 'eureka')) $bid = $brandId['eureka'];
    elseif (str_contains($n, 'wonderful')) $bid = $brandId['wonderful'];
    DB::table('m_products')->where('id', $p->id)->update(['brand_id' => $bid]);
}

// 356 Máy đánh giày
$dgVanPhong = $ensureCat('Máy đánh giày văn phòng', 'may-danh-giay-van-phong', 356, 'attribute');
$dgGiaRe = $ensureCat('Máy đánh giày giá rẻ', 'may-danh-giay-gia-re', 356, 'attribute');
$dgKumisai = $ensureCat('Máy đánh giày Kumisai', 'may-danh-giay-kumisai', 356, 'brand');
$dgShiny = $ensureCat('Máy đánh giày Shiny', 'may-danh-giay-shiny', 356, 'brand');
$dgApus = $ensureCat('Máy đánh giày Apus', 'may-danh-giay-apus', 356, 'brand');
$linkBrand($dgKumisai, 'kumisai');
$linkBrand($dgShiny, 'shiny');
$linkBrand($dgApus, 'brother apus');
echo '356 moved: ' . $assign(356, [
    'giá rẻ' => $dgGiaRe,
    'văn phòng' => $dgVanPhong,
    'công sở' => $dgVanPhong,
    'khách sạn' => $dgVanPhong,
    'tại nhà' => $dgVanPhong,
    'shiny' => $dgShiny,
    'kumisai' => $dgKumisai,
    'apus' => $dgApus,
]) . "\n";

// Sửa brand sp 356 theo tên thật
foreach (DB::table('m_products as p')->join('m_category_product as cp', 'cp.product_id', '=', 'p.id')
    ->whereIn('cp.category_id', [$dgVanPhong, $dgGiaRe, $dgKumisai, $dgShiny, $dgApus, 356])
    ->get(['p.id', 'p.name']) as $p) {
    $n = mb_strtolower($p->name, 'UTF-8');
    $bid = $brandId['kumisai'];
    if (str_contains($n, 'shiny')) $bid = $brandId['shiny'];
    elseif (str_contains($n, 'apus')) $bid = $brandId['brother apus'];
    DB::table('m_products')->where('id', $p->id)->update(['brand_id' => $bid]);
}

// 362 Máy bơm mỡ (chưa có sp — tạo con menu)
$bomKumisai = $ensureCat('Máy bơm mỡ Kumisai', 'may-bom-mo-kumisai', 362, 'brand');
$bomBangTay = $ensureCat('Máy bơm mỡ bằng tay', 'may-bom-mo-bang-tay', 362, 'attribute');
$bomKhiNen = $ensureCat('Máy bơm mỡ khí nén', 'may-bom-mo-khi-nen', 362, 'attribute');
$linkBrand($bomKumisai, 'kumisai');

// 363 Máy hút ẩm (chưa có sp — tạo con menu)
$hutAmKumisai = $ensureCat('Máy hút ẩm Kumisai', 'may-hut-am-kumisai', 363, 'brand');
$hutAmFujie = $ensureCat('Máy hút ẩm FujiE', 'may-hut-am-fujie', 363, 'brand');
$hutAmCN = $ensureCat('Máy hút ẩm công nghiệp', 'may-hut-am-cong-nghiep', 363, 'attribute');
$linkBrand($hutAmKumisai, 'kumisai');
$linkBrand($hutAmFujie, 'fujie');

// ---------- Phân khúc giá ----------
$bands = [
    ['Dưới 5 triệu', null, 5000000],
    ['Từ 5 - 10 triệu', 5000000, 10000000],
    ['Từ 10 - 20 triệu', 10000000, 20000000],
    ['Trên 20 triệu', 20000000, null],
];
foreach ([352, 353, 354, 355, 356, 362, 363] as $catId) {
    if (DB::table('m_category_price_bands')->where('category_id', $catId)->exists()) continue;
    foreach ($bands as $i => $b) {
        DB::table('m_category_price_bands')->insert([
            'category_id' => $catId, 'label' => $b[0], 'min_price' => $b[1], 'max_price' => $b[2],
            'order' => $i, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
echo "price bands inserted for 7 categories\n";

// ---------- Thông số ----------
$optId = function (int $attrId, string $name) {
    $r = DB::table('m_attribute_options')->where('attribute_id', $attrId)->where('name', $name)->first();
    return $r ? (int) $r->id : null;
};
$specPlan = [
    352 => [[908, 'Ắc quy 24V'], [911, 'Quét rác công nghiệp ngồi lái']],
    353 => [[901, '3000W'], [902, '220V / 50Hz'], [901, '7500W']],
    354 => [[911, 'Giải nhiệt tuần hoàn nước công nghiệp'], [912, 'Đài Loan - Chính hãng']],
    355 => [[905, '100L'], [905, '200L'], [911, 'Khử ẩm - bảo quản thiết bị, máy ảnh']],
    356 => [[901, '120W'], [901, '200W'], [911, 'Đánh giày tự động']],
    362 => [[901, '5500W'], [902, '220V / 50Hz']],
    363 => [[901, '3000W'], [902, '220V / 50Hz']],
];
foreach ($specPlan as $catId => $rows) {
    foreach ($rows as $i => [$attrId, $optName]) {
        $oid = $optId($attrId, $optName);
        if (!$oid) { echo "MISS option a$attrId '$optName'\n"; continue; }
        DB::table('m_category_specs')->insertOrIgnore([
            'category_id' => $catId, 'attribute_id' => $attrId, 'option_id' => $oid,
            'order' => $i, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
echo "category specs inserted\n";

// ---------- 4. Menu cấp 2 cho danh mục tạo trước ----------
DB::table('m_categories')->whereIn('id', [554, 555])->update(['is_menu' => 1, 'display_type' => 'attribute']);
DB::table('m_categories')->whereIn('id', [556, 557, 558])->update([
    'parent_id' => 351, 'is_menu' => 1, 'display_type' => 'attribute',
]);
echo "554/555 is_menu=1; 556/557/558 -> parent 351, is_menu=1\n";

// ---------- Integrity + cache ----------
$viol = DB::table('m_category_product')->select('product_id')
    ->groupBy('product_id')->havingRaw('COUNT(DISTINCT category_id) > 1')->count();
echo "products with >1 category: $viol\n";
Cache::forget('fe_menu_filters_v1');
Cache::forget('product_categories_all');
echo "DONE\n";
