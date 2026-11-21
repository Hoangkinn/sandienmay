<?php
// Kiem tra toan bo <loc> trong sitemap demo tra ve 200.
// Cach dung: php deploy/sitemap-status.php https://demo.sandienmay.com/sitemap.xml
// Chay tu may local, co dat User-Agent rieng de phan biet voi crawler that khi doc access log.

$url = $argv[1] ?? 'https://demo.sandienmay.com/sitemap.xml';
$ua = 'Mozilla/5.0 (compatible; sitemap-status-check/1.0; local-audit-by-hoang)';

$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERAGENT => $ua, CURLOPT_TIMEOUT => 30]);
$xml = curl_exec($ch);
curl_close($ch);
if (!$xml) {
    fwrite(STDERR, "Khong lay duoc sitemap: $url\n");
    exit(1);
}

$locs = [];
foreach (simplexml_load_string($xml)->url as $u) {
    $locs[] = (string) $u->loc;
}
$locs = array_values(array_unique($locs));
echo 'so URL can kiem: ' . count($locs) . "\n";

$mh = curl_multi_init();
$handles = [];
foreach ($locs as $i => $loc) {
    $c = curl_init($loc);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => $ua,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    curl_multi_add_handle($mh, $c);
    $handles[$i] = $c;
}
$running = 0;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh, 1.0);
} while ($running > 0);

$bad = [];
foreach ($handles as $i => $c) {
    $code = curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    if ($code !== 200) {
        $bad[] = sprintf('%s -> %d', curl_getinfo($c, CURLINFO_EFFECTIVE_URL), $code);
    }
    curl_multi_remove_handle($mh, $c);
    curl_close($c);
}
curl_multi_close($mh);

echo 'khong phai 200: ' . count($bad) . "\n";
foreach ($bad as $b) {
    echo "  $b\n";
}
