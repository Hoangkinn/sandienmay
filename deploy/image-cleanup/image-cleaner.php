<?php

// image-cleaner.php — DỌN KHO ẢNH: đổi tên file xấu + gộp file trùng byte + sửa mọi tham chiếu DB.
// Mặc định DRY-RUN. Thêm &apply=1 mới ghi. Luôn ghi file ánh xạ old->new để hoàn tác (mode=rollback).
// Copy vào public/ của site: https://site/image-cleaner.php?token=...&mode=report|rename|prune|drop|rollback[&apply=1][&only=<duong dan>]
// Đổi token trước khi chạy. Đã kiểm chứng end-to-end local + demo 05/11. Xoá khỏi public/ ngay sau khi dùng.
// Nguồn: deploy/image-cleanup/ (dùng lại cho production).

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (($_GET['token'] ?? '') !== 'zz-fix-2026-11-05') {
    http_response_code(403);
    exit('no');
}

$mode = $_GET['mode'] ?? 'report';
$apply = ($_GET['apply'] ?? '') === '1';
$mapDir = storage_path('app/zz-image-maps');
if (!is_dir($mapDir)) {
    mkdir($mapDir, 0755, true);
}

$root = public_path('storage');
$db = DB::connection();

/** Quét toàn bộ file trong public/storage */
function disk_files(string $root): array
{
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
    return $files;
}

/** Cột nào có thể chứa đường dẫn ảnh */
function image_columns($db): array
{
    return $db->select("
        SELECT TABLE_NAME t, COLUMN_NAME c
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','json')
          AND (CHARACTER_MAXIMUM_LENGTH >= 20 OR CHARACTER_MAXIMUM_LENGTH IS NULL)
          AND LOWER(COLUMN_NAME) REGEXP
              '(image|img|avatar|thumb|photo|icon|logo|banner|file|attachment|media|preview|url|content|excerpt|description|value)'
    ");
}

/**
 * Đổi tên gốc sang dạng chuẩn SEO: bỏ hậu tố "(1)" " 1", bỏ dấu, bỏ khoảng trắng.
 * Chỉ cắt số 1-2 chữ số cuối để không phá model name (bst-170, kms-1500).
 */
function clean_base_name(string $name): string
{
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);

    $stripped = preg_replace('/\s*\((?:\d+|[A-Za-z])\)/u', '', $base);
    $had_marker = $stripped !== $base;
    $stripped = preg_replace('/(?:\s+\d{1,2})+$/u', '', $stripped);
    $had_marker = $had_marker || $stripped !== $base;

    // " 1" / "(1)" là dấu vết trùng tên do trình duyệt thêm vào. Khi đã chắc chắn file mang
    // dấu vết đó mới cắt tiếp dạng "-1"/"_1" (do chính hệ thống cũ để lại).
    if ($had_marker) {
        $stripped = preg_replace('/(?:[_-]+\d{1,2})+$/u', '', $stripped);
    }

    $slug = Str::slug($stripped);

    if ($slug === '') {
        $slug = 'anh';
    }

    return $ext !== '' ? $slug . '.' . strtolower($ext) : $slug;
}

/**
 * Chỉ file nào thực sự xấu mới đưa vào danh sách đổi tên:
 * có khoảng trắng, có ký tự ngoài ASCII, hoặc mang hậu tố trùng "(1)" / " 1".
 * Không chuẩn hoá tên ASCII sạch chỉ vì viết hoa - đổi 60 tên không lỗi chỉ tạo nhiễu
 * và dễ làm lệch cache ảnh của người dùng.
 */
function needs_clean(string $name): bool
{
    if (preg_match('/\s|[^ -~]/', $name)) {
        return true;
    }

    // Chỉ coi là hậu tố trùng khi nó nối bằng khoảng trắng hoặc trong ngoặc: " (1)", " 1".
    // Dạng "kms-15" là tên model thật, không được đụng vào.
    return (bool) preg_match('/(?:\(\d+\)| \d{1,2})\.\w{2,5}$/', $name);
}

/**
 * Tên ảnh hardcode trong blade/js/css. Ảnh trùng byte không có DB trỏ nhưng có ở đây
 * vẫn đang được giao diện dùng -> không được coi là mồ côi.
 */
function theme_referenced_names(): array
{
    $dirs = array_filter(
        [base_path('resources/views'), public_path('js'), public_path('css'), public_path('css-new')],
        'is_dir'
    );
    $hits = [];
    foreach ($dirs as $dir) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile() || !preg_match('/\.(blade\.php|php|js|css|html)$/i', $f->getPathname())) {
                continue;
            }
            $code = (string) file_get_contents($f->getPathname());
            if (preg_match_all('#[\w\-/.\()\p{L} ]+\.(?:jpg|jpeg|png|gif|webp|svg|ico)#u', $code, $m)) {
                foreach ($m[0] as $token) {
                    $hits[basename(trim($token))] = true;
                }
            }
        }
    }

    return array_keys($hits);
}

/**
 * Đếm số dòng có chứa đúng chuỗi tên file. Bắt buộc escape % và _: LIKE mặc định coi "_"
 * là wildcard một ký tự, mà tên ảnh hệ thống sinh ra đầy dấu "_" -> đếm nhầm cả trăm dòng.
 */
function count_rows_like($db, string $table, string $column, string $needle): int
{
    $row = $db->selectOne(
        "SELECT COUNT(*) n FROM `{$table}` WHERE `{$column}` LIKE ?",
        ['%' . addcslashes($needle, '%_\\') . '%']
    );

    return (int) ($row->n ?? 0);
}

// ============================ ROLLBACK ============================
if ($mode === 'rollback') {
    $mapFile = $mapDir . '/' . basename($_GET['map'] ?? '');
    if (!is_file($mapFile)) {
        exit('khong tim duoc file anh xa: ' . $mapFile);
    }
    $map = json_decode(file_get_contents($mapFile), true);
    $out = ['map' => basename($mapFile), 'apply' => $apply, 'doi_ten' => [], 'khoi_phuc_xoa' => [], 'sua_db' => []];

    $revert_db = function (string $from, string $to) use ($db, $apply, &$out): void {
        foreach (image_columns($db) as $col) {
            $key = $col->t . '.' . $col->c;
            foreach ([$from, rawurlencode($from)] as $needle) {
                $n = count_rows_like($db, $col->t, $col->c, basename($needle));
                if ($n > 0) {
                    if ($apply) {
                        $db->statement("UPDATE `{$col->t}` SET `{$col->c}` = REPLACE(`{$col->c}`, ?, ?)", [basename($needle), basename($to)]);
                    }
                    $out['sua_db'][$key] = ($out['sua_db'][$key] ?? 0) + $n;
                }
            }
        }
    };

    foreach ($map['renames'] as $new => $old) {
        $newAbs = public_path($new);
        $oldAbs = public_path($old);
        if (is_file($newAbs) && !is_file($oldAbs)) {
            if ($apply) {
                @rename($newAbs, $oldAbs);
            }
            $out['doi_ten'][] = $new . ' -> ' . $old . (is_file($oldAbs) ? '' : ' (pending)');
        }
        $revert_db(basename($new), basename($old));
    }

    // Ảnh trùng byte đã chuyển sang thùng rác -> kéo về đúng đường cũ, rồi sửa DB ngược lại.
    foreach ($map['deleted'] ?? [] as $oldRel => $info) {
        $trash = is_array($info) ? ($info['thung_rac'] ?? '') : '';
        $dest = public_path($oldRel);
        if ($trash && is_file($trash) && !is_file($dest)) {
            if ($apply) {
                @rename($trash, $dest);
            }
            $out['khoi_phuc_xoa'][] = $oldRel . (is_file($dest) ? '' : ' (pending)');
            $revert_db(basename($oldRel), is_array($info) ? basename($info['giu'] ?? '') : '');
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// ============================ SCAN ============================
$files = disk_files($root);
$byBase = [];
foreach ($files as $rel => $m) {
    $byBase[basename($rel)][] = $rel;
}

// token anh trong DB -> duong dan dia
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

$refs = [];
$columns = image_columns($db);
foreach ($columns as $col) {
    $key = $col->t . '.' . $col->c;
    $like = [];
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'] as $ext) {
        $like[] = "`{$col->c}` LIKE '%{$ext}%'";
    }
    $rows = $db->select("SELECT `{$col->c}` v FROM `{$col->t}`
                         WHERE `{$col->c}` IS NOT NULL AND `{$col->c}` <> '' AND (" . implode(' OR ', $like) . ')');
    foreach ($rows as $row) {
        if (!is_string($row->v)
            || !preg_match_all('#[\w\-/.\()\p{L} ]+\.(?:jpg|jpeg|png|gif|webp|svg|ico)#u', $row->v, $m)) {
            continue;
        }
        foreach ($m[0] as $token) {
            $p = $resolve(trim($token));
            if ($p !== null) {
                $refs[$p][$key] = ($refs[$p][$key] ?? 0) + 1;
            }
        }
    }
}
$refCount = array_map(fn($w) => array_sum($w), $refs);

$report = [
    'mode' => $mode,
    'apply' => $apply,
    'thoi_gian' => date('Y-m-d H:i:s'),
    'disk' => ['so_file' => count($files), 'tong_bytes' => array_sum(array_column($files, 'bytes'))],
];

// ============================ KE HOACH ============================
// 1) File tên xấu -> tên sạch (giữ thư mục cũ). File đang có DB trỏ thì bắt buộc đổi kèm UPDATE.
$renames = [];
foreach ($files as $rel => $m) {
    $base = basename($rel);
    if (!needs_clean($base)) {
        continue;
    }
    $clean = clean_base_name($base);
    $target = dirname($rel) . '/' . $clean;

    // trùng tên đích với file khác đang tồn tại -> thêm -1, -2
    $i = 1;
    while (isset($files[$target])) {
        $ext = pathinfo($clean, PATHINFO_EXTENSION);
        $target = dirname($rel) . '/' . pathinfo($clean, PATHINFO_FILENAME) . '-' . $i++ . '.' . $ext;
    }

    // an toàn: tên cũ không được là chuỗi con của tên file khác (REPLACE sẽ sửa nhầm)
    $unsafe = false;
    foreach ($byBase as $other => $paths) {
        if ($other !== $base && Str::contains($other, $base)) {
            $unsafe = true;
            break;
        }
    }

    $renames[$rel] = [
        'new' => $target,
        'db_tro' => $refCount[$rel] ?? 0,
        'ly_do' => 'ten_khong_chuan',
        'unsafe_substring' => $unsafe,
    ];
}
$report['doi_ten'] = $renames;

// 2) File trùng byte: giữ file đang được DB dùng, các bản còn lại trỏ về file giữ
$collapses = [];
$themeUsed = array_flip(theme_referenced_names());
$used = function (string $rel) use ($refCount, $themeUsed): bool {
    return ($refCount[$rel] ?? 0) > 0 || isset($themeUsed[basename($rel)]);
};
$byMd5 = [];
foreach ($files as $rel => $m) {
    $byMd5[$m['md5']][] = $rel;
}
foreach ($byMd5 as $md5 => $paths) {
    if (count($paths) < 2) {
        continue;
    }
    $kept = null;
    foreach ($paths as $p) {
        if ($used($p) && !isset($renames[$p])) {
            $kept = $p;
            break;
        }
    }
    if ($kept === null) {
        // không bản nào đang được tham chiếu -> chỉ báo cáo, không tự xoá
        $collapses[] = ['nhom' => $md5, 'giu' => null, 'duong_dan' => $paths, 'ghi_chu' => 'khong_file_nao_duoc_db_tro'];
        continue;
    }
    foreach ($paths as $p) {
        if ($p !== $kept && !isset($renames[$p]) && !$used($p)) {
            $collapses[] = ['nhom' => $md5, 'giu' => $kept, 'xoa' => $p, 'bytes' => $files[$p]['bytes']];
        }
    }
}
$report['gop_trung_byte'] = $collapses;

// 3) File không DB nào trỏ (chỉ báo cáo — blade/js/css có thể đang hardcode tên ảnh)
$unreferenced = [];
foreach ($files as $rel => $m) {
    if (!isset($refCount[$rel])) {
        $unreferenced[$rel] = ['bytes' => $m['bytes'], 'blade_hardcode' => isset($themeUsed[basename($rel)])];
    }
}
$report['file_khong_db_tro'] = ['total' => count($unreferenced), 'danh_sach' => array_slice($unreferenced, 0, 120, true)];

// only=<chuỗi> : chỉ áp dụng cho các đường dẫn chứa chuỗi này. Report vẫn hiện toàn bộ hiện trạng
// để đọc số liệu, còn thao tác ghi giới hạn trong một nhóm file (dùng khi chạy dò từng bước).
$only = trim((string) ($_GET['only'] ?? ''));
if ($only !== '') {
    $renames = array_filter($renames, fn($rel) => stripos($rel, $only) !== false, ARRAY_FILTER_USE_KEY);
    $collapses = array_values(array_filter($collapses, function ($c) use ($only) {
        $paths = isset($c['xoa']) ? [$c['xoa'], $c['giu']] : $c['duong_dan'];
        foreach ($paths as $p) {
            if ($p !== null && stripos($p, $only) !== false) {
                return true;
            }
        }

        return false;
    }));
}
$report['pham_vi_ap_dung'] = ['only' => $only, 'doi_ten' => count($renames), 'gop_trung' => count(array_filter($collapses, fn($c) => isset($c['xoa'])))];

// Danh sách file mode=drop sẽ vứt. Tính ở đây để bản dry-run hiện ra mà đọc trước,
// và để bản apply đi đúng theo danh sách đã duyệt.
$dropList = [];
if ($mode === 'drop') {
    if ($only === '') {
        $report['loai'] = 'mode=drop bat buoc kem only=<duong dan> de tranh xoan ca kho';
    } else {
        foreach ($unreferenced as $rel => $meta) {
            if (!$meta['blade_hardcode'] && stripos($rel, $only) !== false) {
                $dropList[$rel] = $meta;
            }
        }
    }
    $report['drop_danh_sach'] = $dropList;
    $report['drop_tong_bytes'] = array_sum(array_column($dropList, 'bytes'));
}

// ============================ THI HANH ============================
// mode=rename đổi tên + sửa DB; mode=prune gom ảnh trùng byte; mode=drop vứt ảnh không ai trỏ.
// Tách bạch để chạy từng bước riêng và hoàn tác riêng.
if ($apply && in_array($mode, ['rename', 'prune', 'drop'], true)) {
    $done = ['doi_ten' => [], 'sua_db' => [], 'xoa' => []];

    $apply_db_rename = function (string $from, string $to) use ($db, &$done): void {
        foreach (image_columns($db) as $col) {
            $key = $col->t . '.' . $col->c;
            foreach ([$from, rawurlencode($from)] as $needle) {
                $n = count_rows_like($db, $col->t, $col->c, basename($needle));
                if ($n > 0) {
                    $db->statement("UPDATE `{$col->t}` SET `{$col->c}` = REPLACE(`{$col->c}`, ?, ?)", [basename($needle), $to]);
                    $done['sua_db'][$key] = ($done['sua_db'][$key] ?? 0) + $n;
                }
            }
        }
    };

    if ($mode === 'rename') {
        foreach ($renames as $old => $plan) {
            if ($plan['unsafe_substring']) {
                $done['bo_qua'][$old] = 'ten_cu_la_chuoi_con_cua_file_khac';
                continue;
            }
            $oldAbs = public_path($old);
            $newAbs = public_path($plan['new']);
            if (!is_file($oldAbs) || is_file($newAbs)) {
                $done['bo_qua'][$old] = 'duong_dan_dich_da_ton_tai';
                continue;
            }
            rename($oldAbs, $newAbs);
            $done['doi_ten'][$old] = $plan['new'];
            $apply_db_rename(basename($old), basename($plan['new']));
        }
    }

    if ($mode === 'prune' || $mode === 'drop') {
        $trashDir = storage_path('app/zz-image-trash/' . date('Ymd-His'));
        $candidates = [];
        if ($mode === 'prune') {
            foreach ($collapses as $c) {
                if (isset($c['xoa'])) {
                    $candidates[] = ['duong_dan' => $c['xoa'], 'giu' => $c['giu']];
                }
            }
        } else {
            // drop = vứt file disc không ai trỏ (DB lẫn blade/js/css), theo danh sách đã duyệt ở dry-run.
            foreach ($dropList as $rel => $meta) {
                $candidates[] = ['duong_dan' => $rel, 'giu' => null];
            }
        }

        foreach ($candidates as $c) {
            $rel = $c['duong_dan'];
            $abs = public_path($rel);
            if (!is_file($abs) || $used($rel)) {
                continue;
            }
            if (!is_dir($trashDir)) {
                mkdir($trashDir, 0755, true);
            }
            $trashFile = $trashDir . '/' . str_replace('/', '__', $rel);
            rename($abs, $trashFile); // không xoá hẳn: hoàn tác được tới khi chắc chắn ảnh thừa thật
            $done['xoa'][$rel] = ['giu' => $c['giu'], 'thung_rac' => $trashFile];
        }
        if (!empty($done['xoa'])) {
            $report['thung_rac'] = $trashDir;
        }
    }

    $mapFile = $mapDir . '/map-' . date('Ymd-His') . '.json';
    file_put_contents($mapFile, json_encode([
        'renames' => array_flip($done['doi_ten']), // new -> old
        'deleted' => $done['xoa'],
        'db_updates' => $done['sua_db'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    $report['ket_qua'] = $done;
    $report['file_hoan_tac'] = ['ten' => basename($mapFile), 'lenh' => '?mode=rollback&map=' . basename($mapFile) . '&apply=1'];
}

$report['tom_tat'] = [
    'so_file_ten_xau' => count($renames),
    'so_file_trung_byte_co_the_gop' => count(array_filter($collapses, fn($c) => isset($c['xoa']))),
    'so_nhom_trung_khong_rut_gon_duoc' => count(array_filter($collapses, fn($c) => !isset($c['xoa']))),
    'file_khong_db_tro' => count($unreferenced),
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
