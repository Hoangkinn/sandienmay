<?php
// One-shot deploy probe for sandienmay.com — DELETE after use.
// ?k=KEY&do=counts|dump|import|clean
$KEY = 'zzk7f3mQ2x9Lw4Rp';
if (($_GET['k'] ?? '') !== $KEY) { http_response_code(404); exit('Not Found'); }

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$TABLES = [
    'm_attributes', 'm_attribute_options', 'm_categories', 'm_products',
    'm_category_product', 'm_product_images', 'm_product_attributes',
    'brands', 'category_brand_pivot', 'm_category_specs', 'm_category_price_bands',
];

$do = $_GET['do'] ?? 'counts';
header('Content-Type: text/plain; charset=utf-8');

// lọc bảng theo ?tables=a,b,c (whitelist trong $TABLES)
if (!empty($_GET['tables'])) {
    $want = array_intersect($TABLES, explode(',', $_GET['tables']));
    if ($want) $TABLES = array_values($want);
}

if ($do === 'counts') {
    foreach ($TABLES as $t) {
        try {
            echo $t, '=', DB::selectOne("SELECT COUNT(*) c FROM `$t`")->c, "\n";
        } catch (Throwable $e) {
            echo $t, '=ERROR: ', $e->getMessage(), "\n";
        }
    }
    echo 'PHP=', PHP_VERSION, ' ENV=', config('app.env'), ' NOINDEX=', config('app.no_index', '?'), "\n";
    exit;
}

if ($do === 'dump') {
    $out = "-- prod backup " . date('c') . "\nSET FOREIGN_KEY_CHECKS=0;\n";
    foreach ($TABLES as $t) {
        $create = DB::selectOne("SHOW CREATE TABLE `$t`")->{'Create Table'};
        $out .= "DROP TABLE IF EXISTS `$t`;\n$create;\n";
        $rows = DB::select("SELECT * FROM `$t`");
        foreach ($rows as $row) {
            $cols = array_map(fn($c) => "`$c`", array_keys((array)$row));
            $vals = array_map(function ($v) {
                if ($v === null) return 'NULL';
                return DB::connection()->getPdo()->quote((string)$v);
            }, array_values((array)$row));
            $out .= 'INSERT INTO `' . $t . '` (' . implode(',', $cols) . ') VALUES(' . implode(',', $vals) . ");\n";
        }
        echo $t, ': ', count($rows), " rows\n";
    }
    file_put_contents(__DIR__ . '/zz-prod-backup.sql', $out);
    echo 'BYTES=', strlen($out), "\n";
    exit;
}

if ($do === 'import') {
    $file = __DIR__ . '/zz-local-data.sql';
    if (!is_file($file)) { echo "NO FILE\n"; exit; }
    $sql = file_get_contents($file);
    $sql = preg_replace('/^\s*--[^\n]*(\n|$)/m', '', $sql);       // bỏ dòng comment
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);               // bỏ block comment
    $stmts = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql)));
    $pdo = DB::connection()->getPdo();
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    DB::beginTransaction();
    $n = 0;
    try {
        foreach ($stmts as $s) {
            if ($s === '') continue;
            $pdo->exec($s);
            $n++;
        }
        if ($pdo->inTransaction()) DB::commit(); // DDL (DROP/CREATE) tự commit, không còn transaction
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) DB::rollBack();
        echo 'IMPORT FAIL at stmt ', $n, ': ', $e->getMessage(), "\n";
        exit;
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    echo "IMPORTED stmts=$n\n";
    foreach ($TABLES as $t) {
        echo $t, '=', DB::selectOne("SELECT COUNT(*) c FROM `$t`")->c, "\n";
    }
    try { Illuminate\Support\Facades\Artisan::call('cache:clear'); echo "cache cleared\n"; } catch (Throwable $e) { echo 'cache: ', $e->getMessage(), "\n"; }
    exit;
}

if ($do === 'clean') {
    @unlink(__DIR__ . '/zz-prod-backup.sql');
    @unlink(__DIR__ . '/zz-local-data.sql');
    @unlink(__FILE__);
    echo "cleaned\n";
    exit;
}

echo "unknown do\n";
