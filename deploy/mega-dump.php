<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sandienmay;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $pdo->query("SELECT id, parent_id, name, display_type, is_menu, `order` FROM m_categories WHERE is_menu = 1 ORDER BY parent_id IS NOT NULL, `order`, id")->fetchAll(PDO::FETCH_ASSOC);
$byParent = [];
foreach ($rows as $r) {
    $byParent[$r['parent_id'] ?: 0][] = $r;
}
foreach ($byParent[0] as $p) {
    echo "TOP #{$p['id']} {$p['name']} (sort {$p['sort']})\n";
    foreach ($byParent[$p['id']] ?? [] as $c) {
        $n = count($byParent[$c['id']] ?? []);
        echo "   - #{$c['id']} [{$c['display_type']}] {$c['name']} ({$n} con)\n";
    }
}
