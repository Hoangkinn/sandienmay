<?php
// Seed local test data modeled on dqctech.vn scraped names.
// Products: 1 product = exactly 1 category. Posts: 1 post = 1 category.
// Image: single file 1793688706_sp.jpg already in storage/app/public.
error_reporting(0);

require 'D:/laragon/www/sandienmay/vendor/autoload.php';
$app = require 'D:/laragon/www/sandienmay/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$now = now();
$imgProduct = '/images/san-pham-test.jpg'; // ảnh sp đặt sẵn ở public/images
$imgPost = 'tin-tuc-test.jpg'; // copy từ public/images vào public/storage (local: /storage/ phục vụ từ public/storage, không phải symlink)

$dqc = json_decode(file_get_contents('D:/laragon/www/sandienmay/deploy/scrape/dqc-data.json'), true);

// ---------- Ensure categories ----------
$ensureCat = function (string $name, string $slug, ?int $parentId, string $type) use ($now) {
    $row = DB::table('m_categories')->where('slug', $slug)->first();
    if ($row) {
        return (int) $row->id;
    }
    return (int) DB::table('m_categories')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'parent_id' => $parentId,
        'type' => $type,
        'status' => 'actived',
        'is_menu' => 0,
        'is_home_page' => 0,
        'seo_title' => $name,
        'seo_description' => $name,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
};

$catDon = $ensureCat('Máy chà sàn đơn', 'may-cha-san-don', 351, 'product');
$catNgoiLai = $ensureCat('Máy chà sàn ngồi lái', 'may-cha-san-ngoi-lai', 351, 'product');
$catGiatTham = $ensureCat('Máy giặt thảm', 'may-giat-tham', null, 'product');
$catQuatThoi = $ensureCat('Quạt thổi thảm', 'quat-thoi-tham', null, 'product');
$catDanhBong = $ensureCat('Máy đánh bóng sàn', 'may-danh-bong-san', null, 'product');

echo "cats: don=$catDon ngoilai=$catNgoiLai giattham=$catGiatTham quat=$catQuatThoi danhbong=$catDanhBong\n";

// ---------- Products ----------
$usedNames = array_flip(DB::table('m_products')->pluck('name')->map(fn ($n) => mb_strtolower($n, 'UTF-8'))->all());
$usedSlugs = array_flip(DB::table('m_products')->pluck('slug')->all());

$productPlan = [
    // dqc pool slug => [local cat id, max take]
    'may-hut-bui-kumisai' => [357, 24],
    'may-hut-bui-camry' => [359, 10],
    'may-cha-san-don' => [$catDon, 24],
    'may-cha-san-ngoi-lai' => [$catNgoiLai, 24],
    'may-giat-tham' => [$catGiatTham, 10],
    'quat-thoi-tham' => [$catQuatThoi, 10],
    'may-danh-bong-san' => [$catDanhBong, 10],
    'may-hut-bui-cong-nghiep' => [348, 16],
    'may-cha-san' => [351, 20],
    'may-rua-xe' => [353, 24],
    'thap-giai-nhiet' => [354, 22],
    'xe-quet-rac' => [352, 24],
    'tu-chong-am' => [355, 24],
    'may-danh-giay' => [356, 24],
];

$mkSlug = function (string $name) use (&$usedSlugs) {
    $base = Str::slug($name) ?: 'sp';
    $slug = $base;
    $i = 2;
    while (isset($usedSlugs[$slug])) {
        $slug = $base . '-' . $i++;
    }
    $usedSlugs[$slug] = true;
    return $slug;
};

$prodSummary = [];
$inserted = 0;
foreach ($productPlan as $poolSlug => [$catId, $maxTake]) {
    $taken = 0;
    foreach ($dqc['products'][$poolSlug] ?? [] as $name) {
        if ($taken >= $maxTake) {
            break;
        }
        $key = mb_strtolower(trim($name), 'UTF-8');
        if ($key === '' || isset($usedNames[$key])) {
            continue;
        }
        $usedNames[$key] = true;

        $regular = random_int(300, 12000) * 10000; // 3tr - 120tr
        $sale = (int) round($regular * (random_int(68, 88) / 100) / 10000) * 10000;
        $short = "Đây là dữ liệu test mẫu cho sản phẩm {$name}, tham chiếu cấu trúc danh mục từ dqctech.vn.";
        $content = '<p>' . $short . '</p>'
            . '<p>Sản phẩm ' . $name . ' phù hợp cho nhu cầu vệ sinh công nghiệp, nhà xưởng, trung tâm thương mại. '
            . 'Thông số chi tiết sẽ được cập nhật đầy đủ khi lên dữ liệu chính thức.</p>'
            . '<p>Liên hệ hotline để được tư vấn báo giá và xem hàng thực tế.</p>';

        $id = DB::table('m_products')->insertGetId([
            'brand_id' => 0,
            'name' => $name,
            'slug' => $mkSlug($name),
            'image' => $imgProduct,
            'description' => $short,
            'content' => $content,
            'sku' => 'DQC-TEST-' . strtoupper(Str::random(6)),
            'model' => null,
            'quantity' => (string) random_int(5, 60),
            'regular_price' => $regular,
            'sale_price' => $sale,
            'sold_quantity' => random_int(0, 120),
            'stock_status' => 'instock',
            'status' => 'actived',
            'is_new' => random_int(0, 1),
            'is_hot' => random_int(0, 1),
            'order' => 0,
            'seo_title' => $name,
            'seo_description' => $short,
            'view' => random_int(50, 2000),
            'created_at' => $now->copy()->subDays(random_int(1, 90)),
            'updated_at' => $now,
        ]);

        DB::table('m_category_product')->insert([
            'product_id' => $id,
            'category_id' => $catId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('m_product_images')->insert([
            'product_id' => $id,
            'name' => $name . ' - ảnh 1',
            'url' => $imgProduct,
            'thumb_url' => $imgProduct,
            'has_primary' => 1,
        ]);

        $taken++;
        $inserted++;
    }
    $prodSummary[$poolSlug] = $taken;
}
echo "products inserted: $inserted\n";
foreach ($prodSummary as $k => $v) {
    echo "  $k: +$v\n";
}

// ---------- Posts ----------
$supplement = [
    368 => [
        'Top 10 thiết bị vệ sinh công nghiệp bán chạy nhất 2026',
        'Kinh nghiệm chọn mua thiết bị vệ sinh công nghiệp cho nhà xưởng',
        'So sánh máy 1 motor và máy 2 motor trong vệ sinh công nghiệp',
        'Lịch bảo trì thiết bị vệ sinh công nghiệp định kỳ đúng chuẩn',
        'Các lỗi thường gặp của máy vệ sinh công nghiệp và cách khắc phục',
        'Hướng dẫn sử dụng thiết bị vệ sinh công nghiệp an toàn cho ngưởi mới',
    ],
    378 => [
        'Nên mua tủ chống ẩm loại nào tốt nhất hiện nay?',
        'Top 10 tủ chống ẩm bán chạy nhất 2026',
        'Cách sử dụng tủ chống ẩm đúng chuẩn cho ngườI mới',
        'Tủ chống ẩm có tốn điện không? Cách dùng tiết kiệm',
        'So sánh tủ chống ẩm Nikoni và Eirma nên mua loại nào?',
        'Nguyên lý hoạt động của tủ chống ẩm hút ẩm',
        'Hướng dẫn vệ sinh tủ chống ẩm định kỳ',
        'Tủ chống ẩm dung tích nào phù hợp để bảo quản máy ảnh?',
    ],
];

$usedTitles = array_flip(DB::table('m_posts')->pluck('title')->map(fn ($t) => mb_strtolower($t, 'UTF-8'))->all());
$usedPostSlugs = array_flip(DB::table('m_posts')->pluck('slug')->all());

$postPlan = [
    // dqc pool slug => [local cat id, max take]
    'tin-tuc-may-rua-xe' => [369, 13],
    'tin-tuc-may-hut-bui' => [374, 13],
    'tin-tuc-xe-quet-rac' => [375, 13],
    'tin-tuc-thap-giai-nhiet' => [376, 13],
    'tin-tuc-may-cha-san' => [377, 13],
    'tin-tuc-tu-chong-am' => [378, 12],
    'tin-tuc-may' => [368, 16],
];

$mkPostSlug = function (string $title) use (&$usedPostSlugs) {
    $base = Str::slug($title) ?: 'bai-viet';
    $slug = $base;
    $i = 2;
    while (isset($usedPostSlugs[$slug])) {
        $slug = $base . '-' . $i++;
    }
    $usedPostSlugs[$slug] = true;
    return $slug;
};

$postSummary = [];
$postsInserted = 0;
foreach ($postPlan as $poolSlug => [$catId, $maxTake]) {
    $taken = 0;
    foreach (array_merge($dqc['news'][$poolSlug] ?? [], $supplement[$catId] ?? []) as $title) {
        if ($taken >= $maxTake) {
            break;
        }
        $title = trim($title);
        $key = mb_strtolower($title, 'UTF-8');
        if ($key === '' || isset($usedTitles[$key])) {
            continue;
        }
        $usedTitles[$key] = true;

        $body = '<p>Bài viết "' . $title . '" là dữ liệu test tham chiếu từ cấu trúc tin tức của dqctech.vn, '
            . 'dùng để kiểm tra hiển thị danh mục và phân trang trên bản demo.</p>'
            . '<p>Nội dung chi tiết sẽ được biên soạn lại hoàn chỉnh khi triển khai dữ liệu chính thức. '
            . 'Mọi thông số, giá bán và đánh giá trong bài chỉ mang tính mẫu để kiểm thử giao diện.</p>'
            . '<p>Để được tư vấn chi tiết, quý khách vui lòng liên hệ hotline hoặc để lại thông tin qua form báo giá.</p>';
        $plain = strip_tags($body);

        $id = DB::table('m_posts')->insertGetId([
            'title' => $title,
            'slug' => $mkPostSlug($title),
            'excerpt' => mb_substr($plain, 0, 160),
            'image' => $imgPost,
            'content' => $body,
            'order' => 0,
            'status' => 'actived',
            'type' => 'post',
            'seo_title' => $title,
            'seo_description' => mb_substr($plain, 0, 200),
            'count_view' => random_int(100, 3000),
            'is_featured' => 0,
            'created_at' => $now->copy()->subDays(random_int(1, 120)),
            'updated_at' => $now,
        ]);
        DB::table('m_category_post')->insert([
            'post_id' => $id,
            'category_id' => $catId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $taken++;
        $postsInserted++;
    }
    $postSummary[$poolSlug] = $taken;
}
echo "posts inserted: $postsInserted\n";
foreach ($postSummary as $k => $v) {
    echo "  $k: +$v\n";
}

// ---------- Integrity check: no product in 2 categories ----------
$viol = DB::table('m_category_product')
    ->select('product_id')
    ->groupBy('product_id')
    ->havingRaw('COUNT(DISTINCT category_id) > 1')
    ->pluck('product_id');
echo "products with >1 category: " . $viol->count() . ($viol->isEmpty() ? " (OK)\n" : ' IDs: ' . $viol->implode(',') . "\n");

echo "DONE\n";
