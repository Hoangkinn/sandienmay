<?php
// Scrape dqctech.vn: product names per category page, news titles per tin-tuc page.
// Output: D:/laragon/www/sandienmay/deploy/scrape/dqc-data.json
error_reporting(0);

$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

$productCats = [
    'may-hut-bui-cong-nghiep' => 'may-hut-bui-cong-nghiep',
    'may-hut-bui-kumisai' => 'may-hut-bui-kumisai',
    'may-hut-bui-camry' => 'may-hut-bui-camry',
    'may-cha-san' => 'may-cha-san',
    'may-cha-san-don' => 'may-cha-san-don',
    'may-cha-san-ngoi-lai' => 'may-cha-san-ngoi-lai',
    'may-rua-xe' => 'may-rua-xe',
    'thap-giai-nhiet' => 'thap-giai-nhiet',
    'xe-quet-rac' => 'xe-quet-rac',
    'tu-chong-am' => 'tu-chong-am',
    'may-danh-giay' => 'may-danh-giay',
    'may-giat-tham' => 'may-giat-tham',
    'quat-thoi-tham' => 'quat-thoi-tham',
    'may-danh-bong-san' => 'may-danh-bong-san',
];

$newsCats = [
    'tin-tuc-may' => 'tin-tuc-may',
    'tin-tuc-may-rua-xe' => 'tin-tuc-may-rua-xe',
    'tin-tuc-xe-quet-rac' => 'tin-tuc-xe-quet-rac',
    'tin-tuc-may-hut-bui' => 'tin-tuc-may-hut-bui',
    'tin-tuc-may-cha-san' => 'tin-tuc-may-cha-san',
    'tin-tuc-thap-giai-nhiet' => 'tin-tuc-thap-giai-nhiet',
    'tin-tuc-tu-chong-am' => 'tin-tuc-tu-chong-am',
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

function extractProducts(string $html): array
{
    // product card: <h3 class="product-name">NAME</h3>
    preg_match_all('/<h3 class="product-name">([^<]+)<\/h3>/', $html, $m);
    $names = array_values(array_unique(array_map('htmlDecode', $m[1])));
    // also try alternate: class="p-name"
    if (empty($names)) {
        preg_match_all('/class="(?:p-name|product-title|name)">([^<]{5,150})</', $html, $m2);
        $names = array_values(array_unique(array_map('htmlDecode', $m2[1])));
    }
    return $names;
}

function extractNews(string $html): array
{
    $titles = [];
    // common blog card patterns
    if (preg_match_all('/class="(?:entry-title|post-title|news-title|blog-title|item-title)"[^>]*>\s*<a[^>]*>([^<]+)<\/a>/', $html, $m)) {
        $titles = array_map('htmlDecode', $m[1]);
    }
    if (empty($titles) && preg_match_all('/<h[23][^>]*class="[^"]*(?:title|name)[^"]*"[^>]*>\s*<a[^>]*>([^<]+)<\/a>/', $html, $m2)) {
        $titles = array_map('htmlDecode', $m2[1]);
    }
    // fallback: any <h3>...<a>...</a></h3>
    if (empty($titles) && preg_match_all('/<h3[^>]*>\s*<a[^>]*>([^<]{10,200})<\/a>/', $html, $m3)) {
        $titles = array_map('htmlDecode', $m3[1]);
    }
    return array_values(array_unique($titles));
}

$out = ['products' => [], 'news' => [], 'fail' => []];

foreach ($productCats as $slug) {
    $html = fetch("https://dqctech.vn/$slug", $ua);
    if ($html === '') { $out['fail'][] = $slug; continue; }
    $names = extractProducts($html);
    $out['products'][$slug] = $names;
    echo "PRODUCT $slug: " . count($names) . "\n";
}

foreach ($newsCats as $slug) {
    $html = fetch("https://dqctech.vn/$slug", $ua);
    if ($html === '') { $out['fail'][] = $slug; continue; }
    $titles = extractNews($html);
    $out['news'][$slug] = $titles;
    echo "NEWS $slug: " . count($titles) . "\n";
}

file_put_contents('D:/laragon/www/sandienmay/deploy/scrape/dqc-data.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "DONE. fails: " . json_encode($out['fail']) . "\n";
