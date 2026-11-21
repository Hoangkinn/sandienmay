<?php
// Bổ sung cho 241 sp test: thương hiệu (m_products.brand_id) + thông số kỹ thuật
// (m_product_attributes qua option của 13 attribute tskt-* id 900-912).
// Chạy sau seed-test-data.php.
error_reporting(0);

require 'D:/laragon/www/sandienmay/vendor/autoload.php';
$app = require 'D:/laragon/www/sandienmay/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$now = now();
$UID = 39; // user_id dùng trong fixture hiện có

// ---------- Brands ----------
$brandRows = DB::table('brands')->get(['id', 'name']);
$brandId = [];
foreach ($brandRows as $b) {
    $brandId[mb_strtolower($b->name, 'UTF-8')] = (int) $b->id;
}
$ensureBrand = function (string $name) use (&$brandId, $now) {
    $key = mb_strtolower($name, 'UTF-8');
    if (!isset($brandId[$key])) {
        $brandId[$key] = (int) DB::table('brands')->insertGetId([
            'name' => $name,
            'image' => null,
            'is_active' => 1,
            'order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        echo "new brand: $name ({$brandId[$key]})\n";
    }
    return $brandId[$key];
};

// Tạo trước các brand còn thiếu
foreach (['Liang Chi', 'Chaobao', 'Kungfu Clean', 'Karcher', 'IPC', 'Clean Tech'] as $b) {
    $ensureBrand($b);
}

$resolveBrand = function (string $name) use ($brandId) {
    $n = mb_strtolower($name, 'UTF-8');
    $map = [
        'kumisai' => 'kumisai',
        'camry' => 'camry',
        'palada' => 'palada',
        'dqc' => 'dqc-tech',
        'superclean' => 'superclean',
        'supper clean' => 'superclean',
        'alpha' => 'alpha',
        'lutian' => 'lutian',
        'liang chi' => 'liang chi',
        'chaobao' => 'chaobao',
        'kungfu' => 'kungfu clean',
        'karcher' => 'karcher',
        'ipc' => 'ipc',
        'clean tech' => 'clean tech',
    ];
    foreach ($map as $needle => $brandKey) {
        if (str_contains($n, $needle)) {
            return $brandId[$brandKey];
        }
    }
    return $brandId['kumisai']; // fallback
};

// ---------- Attribute option cache ----------
$optionCache = []; // "attrId|value" => optionId
$ensureOption = function (int $attrId, string $value) use (&$optionCache, $now, $UID) {
    $key = $attrId . '|' . $value;
    if (!isset($optionCache[$key])) {
        $row = DB::table('m_attribute_options')
            ->where('attribute_id', $attrId)->where('name', $value)->first();
        if ($row) {
            $optionCache[$key] = (int) $row->id;
        } else {
            $optionCache[$key] = (int) DB::table('m_attribute_options')->insertGetId([
                'name' => $value,
                'position' => 1,
                'status' => 'actived',
                'attribute_id' => $attrId,
                'user_id' => $UID,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
    return $optionCache[$key];
};

// Attribute ids (tskt-*)
$A = [
    'model' => 900, 'cong_suat' => 901, 'dien_ap' => 902, 'so_dong_co' => 903,
    'luu_luong' => 904, 'dung_tich' => 905, 'duong_kinh' => 906, 'chat_lieu' => 907,
    'day_dien' => 908, 'kich_thuoc' => 909, 'trong_luong' => 910,
    'chuc_nang' => 911, 'xuat_xu' => 912,
];

$pick = fn (array $a) => $a[array_rand($a)];
$kichThuoc = fn () => (random_int(30, 90) * 10) . ' x ' . (random_int(30, 70) * 10) . ' x ' . (random_int(60, 130) * 10) . ' mm';
$trongLuong = fn () => random_int(15, 90) . ' Kg';

// Thông số theo nhóm danh mục — trả về [attrId => value]
$specFor = function (int $catId, string $name, string $model) use ($A, $pick, $kichThuoc, $trongLuong) {
    $base = [
        $A['model'] => $model,
        $A['kich_thuoc'] => $kichThuoc(),
        $A['trong_luong'] => $trongLuong(),
        $A['xuat_xu'] => $pick(['Italy - Chính hãng', 'Trung Quốc - Chính hãng', 'Nhật Bản - Chính hãng', 'Đài Loan - Chính hãng']),
    ];
    switch (true) {
        case in_array($catId, [348, 357, 358, 359, 360, 361, 367]): // máy hút bụi
            return $base + [
                $A['cong_suat'] => $pick(['2400W', '3000W', '3600W', '5500W', '7500W']),
                $A['dien_ap'] => $pick(['220V / 50Hz', '380V / 50Hz']),
                $A['so_dong_co'] => $pick(['1 motor', '2 motor', '3 motor']),
                $A['luu_luong'] => random_int(180, 620) . ' L/s',
                $A['dung_tich'] => $pick(['15L', '30L', '60L', '70L', '80L', '90L', '100L']),
                $A['duong_kinh'] => 'φ' . $pick(['360', '400', '440', '500', '600']) . 'mm',
                $A['chat_lieu'] => $pick(['Inox không gỉ', 'Nhựa ABS cao cấp', 'Thép sơn tĩnh điện']),
                $A['day_dien'] => random_int(7, 15) . 'm',
                $A['chuc_nang'] => $pick(['Hút bụi và hút nước', 'Hút bụi khô và ướt', 'Hút bụi - hút nước - hút ẩm']),
            ];
        case in_array($catId, [351, 554, 555, 550, 551, 552]): // máy chà sàn
            return $base + [
                $A['cong_suat'] => $pick(['1100W', '1500W', '1800W', '2200W']),
                $A['dien_ap'] => '220V / 50Hz',
                $A['chuc_nang'] => $pick(['Chà sàn - đánh bóng sàn cứng', 'Chà sàn liên hợp hút nước', 'Chà sàn đơn công nghiệp']),
            ];
        case $catId === 353: // máy rửa xe
            return $base + [
                $A['cong_suat'] => $pick(['2200W', '3000W', '5500W', '7500W', '15000W']),
                $A['dien_ap'] => $pick(['220V / 50Hz', '380V / 50Hz']),
                $A['chuc_nang'] => $pick(['Phun rửa cao áp', 'Rửa xe áp lực cao tự động ngắt']),
            ];
        case $catId === 354: // tháp giải nhiệt
            return $base + [
                $A['chuc_nang'] => 'Giải nhiệt tuần hoàn nước công nghiệp',
                $A['xuat_xu'] => 'Đài Loan - Chính hãng',
            ];
        case $catId === 352: // xe quét rác
            return $base + [
                $A['dien_ap'] => $pick(['Ắc quy 12V', 'Ắc quy 24V', 'Ắc quy 48V']),
                $A['chuc_nang'] => $pick(['Quét rác đường - nhà xưởng', 'Quét rác công nghiệp ngồi lái', 'Quét rác đẩy tay']),
            ];
        case $catId === 355: // tủ chống ẩm
            return $base + [
                $A['dung_tich'] => $pick(['30L', '50L', '100L', '160L', '200L']),
                $A['chuc_nang'] => 'Khử ẩm - bảo quản thiết bị, máy ảnh',
                $A['xuat_xu'] => $pick(['Nhật Bản - Chính hãng', 'Đài Loan - Chính hãng']),
            ];
        case $catId === 356: // máy đánh giày
            return $base + [
                $A['cong_suat'] => $pick(['120W', '150W', '200W']),
                $A['dien_ap'] => '220V / 50Hz',
                $A['chuc_nang'] => 'Đánh giày tự động',
            ];
        case $catId === 556: // máy giặt thảm
            return $base + [
                $A['cong_suat'] => $pick(['1200W', '1500W', '2200W']),
                $A['dien_ap'] => '220V / 50Hz',
                $A['chuc_nang'] => 'Giặt thảm phun hút',
            ];
        case $catId === 557: // quạt thổi thảm
            return $base + [
                $A['cong_suat'] => $pick(['900W', '1200W', '1500W']),
                $A['dien_ap'] => '220V / 50Hz',
                $A['chuc_nang'] => 'Thổi khô thảm - sàn',
            ];
        case $catId === 558: // máy đánh bóng sàn
            return $base + [
                $A['cong_suat'] => $pick(['1100W', '1500W', '2000W']),
                $A['dien_ap'] => '220V / 50Hz',
                $A['chuc_nang'] => 'Đánh bóng sàn cứng tốc độ cao',
            ];
        default:
            return $base;
    }
};

// Trích model từ tên: lấy cụm sau tên hãng
$extractModel = function (string $name) {
    if (preg_match('/(Kumisai|Camry|CAMRY|Palada|Liang Chi|Chaobao|Kungfu Clean|Karcher|IPC|Lutian|DQC\s*-?\s*Tech|Superclean|Supper Clean|Clean Tech)\s+([A-Z0-9][A-Z0-9\.\-\/ ]{1,30})/i', $name, $m)) {
        $model = trim(preg_replace('/\s{2,}/', ' ', $m[2]));
        $model = preg_replace('/\s*\([^)]*\)/', '', $model); // bỏ phần trong ngoặc
        return mb_substr(trim($model), 0, 40) ?: $name;
    }
    return $name;
};

// ---------- Chạy ----------
$products = DB::table('m_products as p')
    ->join('m_category_product as cp', 'cp.product_id', '=', 'p.id')
    ->where('p.image', '/images/san-pham-test.jpg')
    ->get(['p.id', 'p.name', 'cp.category_id']);

$stats = ['brands' => 0, 'specs' => 0];
foreach ($products as $p) {
    $brandIdValue = $resolveBrand($p->name);
    $model = $extractModel($p->name);
    DB::table('m_products')->where('id', $p->id)->update([
        'brand_id' => $brandIdValue,
        'model' => $model,
    ]);
    $stats['brands']++;

    $specs = $specFor((int) $p->category_id, $p->name, $model);
    foreach ($specs as $attrId => $value) {
        $optionId = $ensureOption((int) $attrId, (string) $value);
        DB::table('m_product_attributes')->insertOrIgnore([
            'product_id' => $p->id,
            'attribute_id' => (int) $attrId,
            'option_id' => $optionId,
            'position' => 0,
        ]);
        $stats['specs']++;
    }
}

// Fixture cũ (2281-2307): gán brand + model luôn
$old = DB::table('m_products')->whereBetween('id', [2281, 2307])->get(['id', 'name']);
foreach ($old as $p) {
    $brandIdValue = $resolveBrand($p->name);
    $model = $extractModel($p->name);
    DB::table('m_products')->where('id', $p->id)->update([
        'brand_id' => $brandIdValue,
        'model' => $model,
    ]);
    $stats['brands']++;
}

echo "brands assigned: {$stats['brands']}, spec rows: {$stats['specs']}, options cached: " . count($optionCache) . "\n";

// Kiểm tra sp không có thông số
$noSpecs = DB::table('m_products as p')
    ->leftJoin('m_product_attributes as pa', 'pa.product_id', '=', 'p.id')
    ->where('p.image', '/images/san-pham-test.jpg')->whereNull('pa.id')
    ->count();
echo "seeded products without specs: $noSpecs\n";
echo "products without brand: " . DB::table('m_products')->where('brand_id', 0)->count() . "\n";
echo "DONE\n";
