<?php
// Kiem tra sitemap.xml: XML hop le, trung lap loc, dinh dang lastmod, sai host.
// Dung: php deploy/sitemap-audit.php <url-sitemap>
$url = $argv[1] ?? 'http://sandienmay.test/sitemap.xml';
$xml = file_get_contents($url);
if ($xml === false) { echo "KHONG TAI DUOC: $url\n"; exit(1); }

libxml_use_internal_errors(true);
$doc = simplexml_load_string($xml);
$errors = libxml_get_errors();
echo 'URL: ', $url, PHP_EOL;
echo 'XML parse: ', $errors ? 'LOI' : 'hop le', PHP_EOL;
foreach ($errors as $e) echo '  - ', trim($e->message), ' (dong ', $e->line, ')', PHP_EOL;

$locs = [];
$badLastmod = [];
$offsite = [];
foreach ($doc->url ?? [] as $u) {
    $loc = (string) $u->loc;
    $locs[] = $loc;
    $lm = (string) ($u->lastmod ?? '');
    if ($lm !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)$/', $lm)) {
        $badLastmod[] = $loc . ' -> ' . $lm;
    }
    $host = parse_url($loc, PHP_URL_HOST);
    $want = parse_url($url, PHP_URL_HOST);
    if ($host !== $want) $offsite[] = $loc;
}
$dup = array_filter(array_count_values($locs), fn ($n) => $n > 1);
echo 'so <url>: ', count($locs), ' | doc nhat: ', count(array_unique($locs)), ' | trung lap: ', count($dup), PHP_EOL;
echo 'lastmod sai dinh dang: ', count($badLastmod), PHP_EOL;
foreach (array_slice($badLastmod, 0, 5) as $x) echo '  - ', $x, PHP_EOL;
echo 'khac host: ', count($offsite), PHP_EOL;
foreach (array_slice($offsite, 0, 5) as $x) echo '  - ', $x, PHP_EOL;
echo 'co giao the 50.000 URL: ', count($locs) > 50000 ? 'CO (phai tach sitemap index)' : 'khong', PHP_EOL;
echo '--- 6 <url> dau tien:', PHP_EOL;
foreach (array_slice(explode('<url>', $xml), 1, 6) as $b) {
    echo '  ', trim(str_replace("\n", ' ', substr($b, 0, 160))), PHP_EOL;
}
