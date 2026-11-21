<?php
// Sinh ban webp resize cho 2 anh hard-code cua trang chu (nhom B PageSpeed).
// Chay: "/d/laragon/bin/php/php-8.3.30/php.exe" deploy/gen-webp-b.php
// Chi resize + doi format, KHONG crop -> ti le giu nguyen, render khong doi.

$jobs = [
    // [src, dst, width, quality]
    ['public/images/flash-sale.jpg', 'public/images/flash-sale.webp', 1216, 74],
    ['public/images/flash-sale.jpg', 'public/images/flash-sale-m.webp', 780, 74],
    ['public/images/back-bao.jpg', 'public/images/back-bao.webp', 1216, 74],
    ['public/images/back-bao.jpg', 'public/images/back-bao-m.webp', 780, 74],
];

$root = dirname(__DIR__);
printf("%-34s %-14s %9s %9s %7s\n", 'source', 'output', 'size', 'new KB', 'old KB');
foreach ($jobs as [$src, $dst, $w, $q]) {
    $sp = $root . '/' . $src;
    $info = @getimagesize($sp);
    if (!$info) { printf("%-34s KHONG DOC DUOC\n", $src); continue; }
    [$ow, $oh] = $info;
    $im = imagecreatefromstring(file_get_contents($sp));
    imagepalettetotruecolor($im);
    imagealphablending($im, true);
    imagesavealpha($im, true);

    $nh = (int) round($oh * $w / $ow);
    // imagejpeg/imagewebp phong to khi upscaling; o day chi thu nho nen an toan
    $out = imagescale($im, $w, $nh, IMG_BICUBIC);
    $dp = $root . '/' . $dst;
    if (!imagewebp($out, $dp, $q)) { printf("%-34s LOI ghi webp\n", $src); continue; }
    imagedestroy($im); imagedestroy($out);
    printf("%-34s %-14s %9s %8.1f %8.1f\n", basename($src), basename($dst) . " {$w}x{$nh}",
        number_format(filesize($sp) / 1024, 1) . ' KB', number_format(filesize($dp) / 1024, 1) . ' KB', '');
}
