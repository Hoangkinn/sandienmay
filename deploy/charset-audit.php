<?php
// Phan loai ky tu trong HTML: subset nao (latin / latin-ext / vietnamese / khac) bi cham.
$files = array_slice($argv, 1);
$ranges = [
    'latin' => [[0x0,0xFF],[0x131,0x131],[0x152,0x153],[0x2BB,0x2BC],[0x2C6,0x2C6],[0x2DA,0x2DA],[0x2DC,0x2DC],[0x304,0x304],[0x308,0x308],[0x329,0x329],[0x2000,0x206F],[0x20AC,0x20AC],[0x2122,0x2122],[0x2191,0x2191],[0x2193,0x2193],[0x2212,0x2212],[0x2215,0x2215],[0xFEFF,0xFEFF],[0xFFFD,0xFFFD]],
    'latin-ext' => [[0x0100,0x02BA],[0x02BD,0x02C5],[0x02C7,0x02CC],[0x02CE,0x02D7],[0x02DD,0x02FF],[0x0304,0x0304],[0x0308,0x0308],[0x0329,0x0329],[0x1D00,0x1DBF],[0x1E00,0x1E9F],[0x1EF2,0x1EFF],[0x2020,0x2020],[0x20A0,0x20AB],[0x20AD,0x20C4],[0x2113,0x2113],[0x2C60,0x2C7F],[0xA720,0xA7FF]],
    'vietnamese' => [[0x0102,0x0103],[0x0110,0x0111],[0x0128,0x0129],[0x0168,0x0169],[0x01A0,0x01A1],[0x01AF,0x01B0],[0x0300,0x0301],[0x0303,0x0304],[0x0308,0x0309],[0x0323,0x0323],[0x0329,0x0329],[0x1EA0,0x1EF9],[0x20AB,0x20AB]],
];
$hit = function (int $cp) use ($ranges) {
    $in = [];
    foreach ($ranges as $name => $rr) {
        foreach ($rr as [$a, $b]) if ($cp >= $a && $cp <= $b) { $in[] = $name; break; }
    }
    return $in;
};
foreach ($files as $f) {
    $html = file_get_contents($f);
    $text = preg_replace('~<script.*?</script>|<style.*?</style>|<[^>]+>~us', ' ', $html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $counts = ['latin' => 0, 'vietnamese' => 0, 'latin-ext-any' => 0, 'latin-ext-only' => 0, 'other' => 0];
    $examples = ['latin-ext-only' => [], 'other' => [], 'latin-ext-also' => []];
    foreach (mb_str_split($text, 1, 'UTF-8') as $ch) {
        $cp = mb_ord($ch, 'UTF-8');
        if ($cp <= 0x20 || $cp === 0xA0) continue;
        $in = $hit($cp);
        if (!$in) {
            $counts['other']++;
            if (count($examples['other']) < 12) $examples['other'][] = $ch . '(U+' . strtoupper(dechex($cp)) . ')';
            continue;
        }
        if (in_array('latin', $in, true)) $counts['latin']++;
        if (in_array('vietnamese', $in, true)) $counts['vietnamese']++;
        if (in_array('latin-ext', $in, true)) {
            $counts['latin-ext-any']++;
            $also = array_values(array_diff($in, ['latin-ext']));
            $key = $ch . '(U+' . strtoupper(dechex($cp)) . ')' . ($also ? ' [' . implode('+', $also) . ']' : ' [KHONG SUBSET NAO KHAC]');
            if (!in_array($key, $examples['latin-ext-also'], true) && count($examples['latin-ext-also']) < 15) $examples['latin-ext-also'][] = $key;
            if (!$also) {
                $counts['latin-ext-only']++;
                if (count($examples['latin-ext-only']) < 12) $examples['latin-ext-only'][] = $ch . '(U+' . strtoupper(dechex($cp)) . ')';
            }
        }
    }
    echo basename($f), '  ';
    echo json_encode($counts), '  ';
    echo 'ky tu nam trong latin-ext: ', implode(' ', $examples['latin-ext-also']) ?: '-', PHP_EOL;
    echo '    other: ', implode(' ', $examples['other']) ?: '-', PHP_EOL;
}
