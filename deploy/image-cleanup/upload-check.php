<?php
// upload-check.php — kiem tra duong upload anh chay THAT tren host (PHP + GD cua host), khong can tai khoan admin.
// Copy vao public/ cua site: ?token=...&run=1 (tao file test) | ?token=...&clean=1 (don het).
// File nay phai xoa khoi public/ ngay sau khi dung. Doi token truoc khi chay.
// Lenh: ?token=...&run=1   (tao file test)  |  ?token=...&clean=1  (don het)

$token = 'zz-upc-2026-11-05';
if (($_GET['token'] ?? '') !== $token) {
    http_response_code(403);
    exit('token sai');
}

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\UploadImageHelper;
use Illuminate\Http\UploadedFile;

$folder = 'storage/zzuploadtest';
$dir = public_path($folder);
$out = ['php' => PHP_VERSION, 'gd' => extension_loaded('gd'), 'folder' => $folder];

// Nguon anh that: 2 file khac byte, lay tu anh ton tai trong kho (khong bịa du lieu)
$srcs = glob(public_path('storage') . '/*.png') ?: glob(public_path('storage') . '/*.jpg');
if (count($srcs) < 2) {
    $out['loi'] = 'khong du 2 anh nguon trong public/storage';
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}
$a = sys_get_temp_dir() . '/zza.png';
$b = sys_get_temp_dir() . '/zzb.png';
copy($srcs[0], $a);
copy($srcs[1], $b);
if (md5_file($a) === md5_file($b)) {
    unlink($b);
    $img = imagecreatetruecolor(9, 9);
    imagefill($img, 0, 0, imagecolorallocate($img, 12, 34, 56));
    imagepng($img, $b);
}

// move() co the "tiep th" file nguon nen moi lan upload phai ban ra 1 ban tam riêng
$upCount = 0;
$up = function (string $original, string $src) use (&$upCount) {
    $tmp = sys_get_temp_dir() . '/zzup-' . (++$upCount) . '.png';
    copy($src, $tmp);

    return new UploadedFile($tmp, $original, 'image/png', null, true);
};

if (isset($_GET['clean'])) {
    $deleted = [];
    foreach (glob($dir . '/*') ?: [] as $f) {
        if (is_file($f)) {
            unlink($f);
            $deleted[] = basename($f);
        }
    }
    @rmdir($dir);
    unlink($a);
    unlink($b);
    $out['clean'] = ['da_xoa' => $deleted, 'thu_muc_con_lai' => is_dir($dir)];
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

$cases = [
    ['ten' => 'DẦU HÚT KHE (1) 1.png', 'ky_vong' => 'khong dau, khong khoang trang, bo (1) va so thua'],
    ['ten' => 'ban hut bui (2).png', 'ky_vong' => 'ban-hut-bui.png'],
    ['ten' => 'Máy giặt cửa trên KMS-1500.png', 'ky_vong' => 'giu model name KMS-1500'],
    ['ten' => '(1).png', 'ky_vong' => 'ten rong -> anh.png'],
];

$run = function (string $original, string $src, bool $dedupe, bool $timePrefix) use ($up, $folder) {
    $r = UploadImageHelper::store($up($original, $src), $folder, $dedupe, $timePrefix);

    return ['ten_goc' => $original, 'ten_luu' => $r['name'], 'url' => $r['url'], 'reused' => $r['reused']];
};

if (isset($_GET['run'])) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // 1) Cac ten xau -> phai duoc lam sach
    foreach ($cases as $c) {
        $out['slug'][] = ['ten_goc' => $c['ten'], 'ten_luu' => UploadImageHelper::fileName($up($c['ten'], $a), false), 'ghi_chu' => $c['ky_vong']];
    }

    // 2) Upload that: timePrefix = true (danh ba anh san pham)
    $out['upload'] = [$run('DẦU HÚT KHE (1) 1.png', $a, false, true), $run('Máy giặt cửa trên KMS-1500.png', $a, false, true)];

    // 3) Dedupe theo byte: ban trung phai dung lai file cu, khong ghi them
    $out['dedupe'] = [$run('ban hut bui (2).png', $b, false, true), $run('ban hut bui khac ten.png', $b, true, true)];

    // 4) Khong timePrefix (anh bai viet): trung ten + khac noi dung -> -1, -2
    $out['khong_prefix'] = [$run('anh-banner.png', $a, false, false), $run('anh-banner (1).png', $b, false, false), $run('anh-banner (2).png', $b, true, false)];

    // 5) Kiem dinh ten sinh ra
    $bad = [];
    foreach (glob($dir . '/*') ?: [] as $f) {
        $n = basename($f);
        if (preg_match('/\s|[^ -~]/', $n) || preg_match('/\(\d+\)| \d{1,2}\.\w+$/', $n)) {
            $bad[] = $n;
        }
    }
    $out['kiem_tra'] = ['so_file' => count(glob($dir . '/*') ?: []), 'ten_van_xau' => $bad];
    $out['danh_sach_file'] = array_map('basename', glob($dir . '/*') ?: []);
}

$header = [];
foreach (headers_list() as $h) {
    $header[] = $h;
}
$out['ghi_chu'] = 'Chay lenh: ?token=...&run=1 de tao, ?token=...&clean=1 de don.';
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
