<?php
// Scrape dqctech.vn pages 2-3 for categories that need more items.
error_reporting(0);
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

$jobs = [
    // products: slug => [pages]
    'may-hut-bui-cong-nghiep' => [2],
    'may-hut-bui-kumisai' => [2],
    'may-hut-bui-camry' => [2, 3],
    'may-cha-san' => [2],
    'may-cha-san-don' => [2],
    'may-cha-san-ngoi-lai' => [2],
    'may-rua-xe' => [2, 3],
    'thap-giai-nhiet' => [2, 3],
    'xe-quet-rac' => [2, 3],
    'tu-chong-am' => [2, 3],
    'may-danh-giay' => [2, 3],
    'may-giat-tham' => [2, 3],
    'quat-thoi-tham' => [2, 3],
    'may-danh-bong-san' => [2, 3],
    // news
    'tin-tuc-may' => [2],
    'tin-tuc-may-rua-xe' => [2],
    'tin-tuc-xe-quet-rac' => [2, 3],
    'tin-tuc-may-hut-bui' => [2],
    'tin-tuc-may-cha-san' => [2],
    'tin-tuc-thap-giai-nhiet' => [2],
    'tin-tuc-tu-chong-am' => [2, 3],
];

function fetch(string $url, string $ua): string
{
    $ctx = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => "User-Agent: $ua\r\nAccept-Language: vi-VN,vi;q=0.9\r\n",
        'timeout' => 30,
        'follow_location' => 1,
    ], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $html = @file_get_contents($url, false, $ctx);
    return $html === false ? '' : $html;
}
function htmlDecode(string $s): string
{
    return html_entity_decode(trim($s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$isNews = fn ($slug) => str_starts_with($slug, 'tin-tuc');
$out = json_decode(file_get_contents('D:/laragon/www/sandienmay/deploy/scrape/dqc-data.json'), true);

foreach ($jobs as $slug => $pages) {
    foreach ($pages as $p) {
        $html = fetch("https://dqctech.vn/$slug?page=$p", $ua);
        if ($html === '') { echo "FAIL $slug p$p\n"; continue; }
        if ($isNews($slug)) {
            if (preg_match_all('/class="tech-card"[^>]*>.*?<h4>([^<]+)<\/h4>/s', $html, $m)) {
                $titles = array_map('htmlDecode', $m[1]);
                $before = count($out['news'][$slug] ?? []);
                $out['news'][$slug] = array_values(array_unique(array_merge($out['news'][$slug] ?? [], $titles)));
                echo "NEWS $slug p$p: +" . (count($out['news'][$slug]) - $before) . " (total " . count($out['news'][$slug]) . ")\n";
            } else {
                echo "NEWS $slug p$p: 0 (selector miss)\n";
            }
        } else {
            preg_match_all('/<h3 class="product-name">([^<]+)<\/h3>/', $html, $m);
            $names = array_map('htmlDecode', $m[1]);
            $before = count($out['products'][$slug] ?? []);
            $out['products'][$slug] = array_values(array_unique(array_merge($out['products'][$slug] ?? [], $names)));
            echo "PROD $slug p$p: +" . (count($out['products'][$slug]) - $before) . " (total " . count($out['products'][$slug]) . ")\n";
        }
    }
}

file_put_contents('D:/laragon/www/sandienmay/deploy/scrape/dqc-data.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "saved\n";
