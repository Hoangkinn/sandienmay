<?php
$root = 'D:/laragon/www/sandienmay/deploy/probe-2026-11-19/cache-data';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $raw = file_get_contents($file->getPathname());
    $payload = substr($raw, 10);
    $val = @unserialize($payload);
    if ($val === false && $payload !== 'b:0;') { echo "?? {$file->getPathname()}\n"; continue; }
    if (!is_array($val)) { echo "- scalar at {$file->getPathname()}\n"; continue; }
    $first = reset($val);
    if (is_array($first) && array_key_exists('children', $first)) {
        echo "== TREE entry {$file->getPathname()} (".count($val).") exp=".substr($raw,0,10)."\n";
        $walk = function($nodes, $depth) use (&$walk) {
            foreach ($nodes as $n) {
                echo str_repeat('  ', $depth)."{$n['id']} | {$n['name']} | parent=".var_export($n['parent_id'],true)." | home=".var_export($n['is_home_page'],true)." | feat=".var_export($n['is_featured'] ?? null,true)."\n";
                if (!empty($n['children'])) $walk($n['children'], $depth+1);
            }
        };
        $walk($val, 1);
    } elseif (is_array($first) && array_key_exists('category_id', $first) && array_key_exists('name', $first)) {
        $counts = [];
        foreach ($val as $p) { $counts[$p['category_id']] = ($counts[$p['category_id']] ?? 0) + 1; }
        echo "== PRODUCTS entry {$file->getPathname()} (".count($val).") exp=".substr($raw,0,10)." byCat=".json_encode($counts)."\n";
    } elseif (is_array($first) && array_key_exists('parent_id', $first) && array_key_exists('name', $first)) {
        echo "== CATLIST entry {$file->getPathname()} (".count($val).") exp=".substr($raw,0,10)." ids=".implode(',', array_map(fn($c)=>$c['id'].($c['is_home_page']?'*':''), $val))."\n";
    } else {
        echo "- other {$file->getPathname()} (".count($val).") key0=".(is_array($first)?implode(',', array_slice(array_keys($first),0,6)):gettype($first))."\n";
    }
}
