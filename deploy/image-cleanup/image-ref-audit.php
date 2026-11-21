<?php

// image-ref-audit.php — KIỂM KÊ KHO ẢNH, chỉ đọc (không ghi file, không sửa DB).
// Dùng: copy vào public/ của site, gọi qua https://site/image-ref-audit.php?token=...&limit=80
// Rồi xoá khỏi public/ ngay sau khi chạy. Nguồn lưu ở deploy/image-cleanup/ để replay cho production.

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (($_GET['token'] ?? '') !== 'zz-audit-2026-11-05') {
    http_response_code(403);
    exit('no');
}

$limit = (int) ($_GET['limit'] ?? 80);
$root = public_path('storage');
$db = DB::connection();

// ---------- 1. File trên đĩa ----------
$files = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($it as $f) {
    if ($f->isFile()) {
        $rel = 'storage/' . str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
        $files[$rel] = ['bytes' => $f->getSize(), 'md5' => md5_file($f->getPathname())];
    }
}

$byBase = [];
foreach ($files as $rel => $meta) {
    $byBase[basename($rel)][] = $rel;
}

// DB có thể ghi "x.jpg", "photos/39/x.jpg" hoặc "/storage/photos/39/x.jpg" -> quy về đường đĩa thật
$resolve = function (string $token) use ($files, $byBase): ?string {
    $t = ltrim(str_replace('\\', '/', $token), '/');
    if (isset($files[$t])) {
        return $t;
    }
    if (isset($files['storage/' . $t])) {
        return 'storage/' . $t;
    }
    $base = basename($t);
    return count($byBase[$base] ?? []) === 1 ? $byBase[$base][0] : null;
};

$byExt = [];
foreach ($files as $rel => $meta) {
    $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
    $byExt[$ext] = ($byExt[$ext] ?? 0) + 1;
}

// ---------- 2. Tên xấu ----------
$badNames = [];
foreach ($files as $rel => $meta) {
    $base = basename($rel);
    $reasons = [];
    if (preg_match('/\s/u', $base)) {
        $reasons[] = 'co_khoang_trang';
    }
    if (!preg_match('/^[a-z0-9._-]+$/u', $base)) {
        $reasons[] = preg_match('/\s/u', $base) ? 'them_ky_tu_khac' : 'ky_tu_khong_chuan';
    }
    if (preg_match('/\((?:\d+|[A-Za-z])\)/u', $base)) {
        $reasons[] = 'hau_to_trung';
    }
    if ($reasons) {
        $badNames[$rel] = $reasons;
    }
}

// ---------- 3. Trùng byte ----------
$byMd5 = [];
foreach ($files as $rel => $meta) {
    $byMd5[$meta['md5']][] = $rel;
}
$duplicates = [];
foreach ($byMd5 as $md5 => $paths) {
    if (count($paths) > 1) {
        $duplicates[] = ['bytes' => $files[$paths[0]]['bytes'], 'md5' => $md5, 'paths' => $paths];
    }
}

// ---------- 4. Quét tham chiếu ảnh trong DB ----------
$candidateCols = $db->select("
    SELECT TABLE_NAME t, COLUMN_NAME c
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','json')
      AND CHARACTER_MAXIMUM_LENGTH >= 20
      AND LOWER(COLUMN_NAME) REGEXP
          '(image|img|avatar|thumb|photo|icon|logo|banner|file|attachment|media|preview|url|content|excerpt|description|value)'
");

$refs = [];        // duong dan dia -> ['count' => n, 'where' => [bang.cot => n]]
$notFound = [];    // DB tro vao anh khong ton tai tren dia
$ambiguous = [];   // khong quy duoc duong dan vi trung ten o nhieu thu muc
$outOfScope = [];  // anh thuoc thu muc giao dien (/images/, /img/...), ngoai kho upload
$columnsSeen = [];
$scanned = 0;

foreach ($candidateCols as $col) {
    $key = $col->t . '.' . $col->c;
    $scanned++;
    $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];
    $likeParts = [];
    foreach ($exts as $ext) {
        $likeParts[] = "`{$col->c}` LIKE '%{$ext}%'";
    }
    $rows = $db->select("SELECT `{$col->c}` v FROM `{$col->t}`
                         WHERE `{$col->c}` IS NOT NULL AND `{$col->c}` <> '' AND ("
        . implode(' OR ', $likeParts) . ')');
    if (count($rows) === 0) {
        continue;
    }

    $withImage = 0;
    foreach ($rows as $row) {
        if (!is_string($row->v)
            || !preg_match_all('#[\w\-/.\()\p{L} ]+\.(?:jpg|jpeg|png|gif|webp|svg|ico)#u', $row->v, $m)) {
            continue;
        }
        $withImage++;
        foreach ($m[0] as $token) {
            $token = trim($token);
            $diskPath = $resolve($token);
            if ($diskPath !== null) {
                $refs[$diskPath]['count'] = ($refs[$diskPath]['count'] ?? 0) + 1;
                $refs[$diskPath]['where'][$key] = ($refs[$diskPath]['where'][$key] ?? 0) + 1;
            } elseif (count($byBase[basename($token)] ?? []) > 1) {
                $ambiguous[$token] = ($ambiguous[$token] ?? 0) + 1;
            } elseif (preg_match('#^/?(img|images|css|css-new|js|js-new)/#i', ltrim($token, '/'))) {
                // Ảnh giao diện nằm ngoài kho upload -> không thuộc phạm vi dọn dẹp
                $outOfScope[$token] = ($outOfScope[$token] ?? 0) + 1;
            } else {
                $notFound[$token]['count'] = ($notFound[$token]['count'] ?? 0) + 1;
                $notFound[$token]['where'][$key] = true;
            }
        }
    }
    if ($withImage > 0) {
        $columnsSeen[$key] = $withImage;
    }
}

// ---------- 5. Đối chiếu ----------
$refTable = [];
foreach ($refs as $rel => $info) {
    $refTable[$rel] = ['db_tro' => $info['count'], 'cot' => array_keys($info['where'])];
}
uasort($refTable, fn($a, $b) => $b['db_tro'] <=> $a['db_tro']);

foreach ($badNames as $rel => $reasons) {
    $badNames[$rel] = ['ly_do' => $reasons, 'db_tro' => $refTable[$rel]['db_tro'] ?? 0];
}

$orphans = [];
foreach ($files as $rel => $meta) {
    if (!isset($refs[$rel]) && !str_contains($rel, '/thumbs/')) {
        $orphans[$rel] = $meta['bytes'];
    }
}

ksort($badNames);
ksort($orphans);
arsort($notFound);
arsort($ambiguous);

$summarize = function (array $arr, int $cap): array {
    return ['total' => count($arr), 'hien_thi' => array_slice($arr, 0, $cap, true)];
};

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'meo' => ['app_url' => config('app.url'), 'php' => PHP_VERSION, 'root' => $root, 'ngay' => date('Y-m-d H:i')],
    'disk' => [
        'so_file' => count($files),
        'tong_bytes' => array_sum(array_column($files, 'bytes')),
        'theo_duoi' => $byExt,
        'thu_muc' => array_count_values(array_map(fn($p) => dirname($p), array_keys($files))),
    ],
    'db' => ['so_cot_quet' => $scanned, 'cot_co_anh' => $columnsSeen, 'tong_tham_chieu' => array_sum(array_column($refTable, 'db_tro'))],
    'anh_trung_byte' => $summarize($duplicates, $limit),
    'ten_xau' => $summarize($badNames, $limit),
    'db_tro_anh_khong_co_tren_dia' => $summarize(array_map(
        fn($u) => ['count' => $u['count'], 'cot' => array_keys($u['where'])],
        $notFound
    ), $limit),
    'khong_quy_duoc_duong_dan' => $summarize($ambiguous, $limit),
    'ngoai_pham_vi_anh_giao_dien' => $summarize($outOfScope, $limit),
    'file_khong_ai_tro' => $summarize($orphans, $limit),
    'file_duoc_tro_nhieu_nhat' => $summarize($refTable, $limit),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
