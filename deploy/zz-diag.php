<?php

// zz-diag.php — CHAN MUON site sau khi bo ban moi len sandienmay.com. CHI DOC.
// Dung: upload vao public/, mo https://site/zz-diag.php?token=..., doc xong XOAB file ngay.

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (($_GET['token'] ?? '') !== 'dg-2026-11-05b') {
    http_response_code(403);
    exit('no');
}
header('Content-Type: text/plain; charset=utf-8');

function line(string $k, $v): void
{
    echo str_pad($k, 44, ' ') . (is_bool($v) ? ($v ? 'CO' : 'KHONG') : $v) . "\n";
}

function countFiles(string $dir): array
{
    if (!is_dir($dir)) {
        return [-1, -1];
    }
    $n = 0;
    $b = 0;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($it as $f) {
        if ($f->isFile()) {
            $n++;
            $b += $f->getSize();
        }
    }
    return [$n, $b];
}

echo "===== 1. MOI TRUONG =====\n";
line('PHP_VERSION', PHP_VERSION);
line('laravel', $app->version());
line('APP_ENV (config)', config('app.env'));
line('APP_DEBUG', config('app.debug'));
line('APP_URL', config('app.url'));
line('APP_NO_INDEX (noindex dang bat?)', config('app.no_index'));
line('DB::getDatabaseName()', DB::connection()->getDatabaseName());
line('DB host', config('database.connections.mysql.host'));
line('DB username', config('database.connections.mysql.username'));

echo "\n===== 2. KHOANH CACH DEMO / PROD =====\n";
$demo = '/public_html/demo.sandienmay.com/storage/app/public';
line('demo storage co doc duoc tu site nay?', is_dir($demo) ? 'CO ('.$demo.')' : 'KHONG');
if (is_dir($demo)) {
    [$n, $b] = countFiles($demo);
    line('demo: so file / bytes', $n.' / '.round($b / 1048576, 1).' MB');
}

echo "\n===== 3. KHO ANH CUA SITE NAY =====\n";
$pubStorage = public_path('storage');
$realStorage = storage_path('app/public');
line('public/storage la symlink?', is_link($pubStorage));
line('public/storage realpath', realpath($pubStorage));
line('public/storage == storage/app/public?', realpath($pubStorage) === realpath($realStorage));
[$n1, $b1] = countFiles($pubStorage);
line('public/storage: file / bytes', $n1.' / '.round($b1 / 1048576, 1).' MB');
[$n2, $b2] = countFiles($realStorage);
line('storage/app/public: file / bytes', $n2.' / '.round($b2 / 1048576, 1).' MB');

echo "\n===== 4. DU LIEU TRONG DB dang dung =====\n";
foreach (['m_products', 'm_posts', 'm_categories', 'm_orders', 'm_comments', 'contacts',
    'm_customers', 'email_subscribers', 'histories', 'brands', 'reviews', 'm_category_price_bands',
    'm_category_specs', 'm_product_images', 'm_settings', 'm_setting_fields', 'migrations'] as $t) {
    $exists = DB::select("SHOW TABLES LIKE '".addcslashes($t, '%_\\')."'");
    line($t, empty($exists) ? '>>> THANG BANG <<<' : 'rows = '.DB::table($t)->count());
}
foreach (['m_products', 'm_posts', 'm_orders'] as $t) {
    if (!empty(DB::select("SHOW TABLES LIKE '".$t."'"))) {
        line($t.' max(id)', (string) DB::table($t)->max('id'));
    }
}
line('san pham slug bat dau "demo-"', DB::table('m_products')->where('slug', 'like', 'demo-%')->count());
line('bai viet slug bat dau "demo-"', DB::table('m_posts')->where('slug', 'like', 'demo-%')->count());
line('bai viet noi dung chua "demo.sandienmay.com"',
    DB::table('m_posts')->where('content', 'like', '%demo.sandienmay.com%')->count());
line('setting gia tri chua "demo.sandienmay.com"',
    DB::table('m_setting_fields')->where('field_value', 'like', '%demo.sandienmay.com%')->count());

echo "\n===== 5. ANH DB TRO MA DISC KHONG CO =====\n";
$missing = [];
$checked = 0;
$wanted = [
    'm_product_images' => ['url', 'thumb_url'],
    'm_products' => ['image'],
    'm_projects' => ['image'],
    'm_categories' => ['image', 'icon'],
    'm_banners' => ['image', 'image_mobile'],
    'm_setting_fields' => ['field_value'],
    'm_comments' => ['image'],
    'm_promotions' => ['image'],
    'm_videos' => ['image'],
];
foreach ($wanted as $tbl => $cols) {
    if (empty(DB::select("SHOW TABLES LIKE '".addcslashes($tbl, '%_\\')."'"))) {
        continue;
    }
    foreach ($cols as $col) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn($tbl, $col)) {
            continue;
        }
        foreach (DB::table($tbl)->whereNotNull($col)->where($col, '<>', '')->distinct()->pluck($col) as $u) {
            $u = trim((string) $u);
            // long thang JSON / chuoi dai khong phai duong dan anh
            if ($u === '' || strlen($u) > 400 || str_starts_with($u, '{')) {
                continue;
            }
            if (!preg_match('~\.(png|jpe?g|gif|webp|svg)$~i', $u)) {
                continue;
            }
            $checked++;
            $path = ltrim(preg_replace('~^https?://[^/]+~', '', $u), '/');
            if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/') && !str_starts_with($path, 'uploads/')) {
                $path = 'storage/'.$path;
            }
            if (!is_file(public_path($path))) {
                $missing[$path][$tbl.'.'.$col][] = $u;
            }
        }
    }
}
line('da kiem tra', $checked.' duong dan');
line('thieu', count($missing).' duong dan');
$top = array_slice($missing, 0, 40, true);
foreach ($top as $path => $ref) {
    echo "   404  $path   <- ".implode(',', array_keys($ref))."\n";
}


echo "\n===== 6. FILE RAC o goc site + public =====\n";
foreach ([dirname(__DIR__), __DIR__] as $dir) {
    echo "-- $dir\n";
    foreach (scandir($dir) as $e) {
        if ($e === '.' || $e === '..') {
            continue;
        }
        $p = $dir.'/'.$e;
        if (is_dir($p)) {
            [$n, $b] = countFiles($p);
            printf("   DIR  %-28s %7d file %9.1f MB\n", $e, max($n, 0), max($b, 0) / 1048576);
        } else {
            printf("   FILE %-28s %9d bytes  %s\n", $e, filesize($p), date('Y-m-d H:i', filemtime($p)));
        }
    }
}

echo "\n===== 7. CACHE / LOG =====\n";
line('bootstrap/cache noi dung', implode(', ', array_diff(scandir(base_path('bootstrap/cache')), ['.', '..', '.gitignore'])));
[$nl, $bl] = countFiles(storage_path('logs'));
line('storage/logs: file / bytes', $nl.' / '.round($bl / 1048576, 1).' MB');
[$nv, $bv] = countFiles(storage_path('framework/views'));
line('framework/views (blade compiled)', $nv.' file');
line('storage/framework/', implode(', ', array_diff(@scandir(storage_path('framework')) ?: [], ['.', '..'])));
line('disk con trong (public/)', round(disk_free_space(__DIR__) / 1073741824, 2).' GB');

echo "\n===== 8. 16 ANH homepage bao 404 =====\n";
$probe = [
    'storage/vacuum-cleaner-1793256008.png', 'storage/scrubber-1793256024.png',
    'storage/street-sweeper-1793256037.png', 'storage/pressure-washer-1793256058.png',
    'storage/cooling-1793256069.png', 'storage/camera-1793256079.png',
    'storage/equipment-1793256115.png', 'storage/in-stock-1793256132.png',
    'storage/grease-1793256149.png', 'storage/1793073123_do-dai-tay-can-barrier-tu-dong-bst-170.webp',
    'storage/1793332230_6ae41406f13ec.webp', 'storage/1793332230_mobile_6ae41406f3c05.webp',
    'storage/photos/39/1793688706_sp.jpg', 'storage/1793065875_114e1c3ee4f24c335e153fd99eb46ac1.jpg',
    'images/no-image.png', 'storage/photos/39/_DSC6964.jpg',
];
foreach ($probe as $p) {
    printf("   %-72s prod=%s demo=%s\n", $p,
        is_file(public_path($p)) ? 'OK' : '404',
        is_file('/public_html/demo.sandienmay.com/public/'.$p) ? 'OK' : '-');
}

echo "\n===== 9. DB NAO CON TON TAI (tu goi lenh hien tai) =====\n";
try {
    foreach (DB::select('SHOW DATABASES') as $d) {
        echo "   database: ".implode('', (array) $d)."\n";
    }
} catch (\Throwable $e) {
    line('SHOW DATABASES', 'BI CHAN: '.$e->getMessage());
}
try {
    foreach (DB::select('SHOW GRANTS') as $g) {
        echo "   grants: ".implode('', (array) $g)."\n";
    }
} catch (\Throwable $e) {
    line('SHOW GRANTS', 'BI CHAN');
}

