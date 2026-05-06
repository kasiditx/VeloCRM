<?php
$viewsDir = __DIR__ . '/resources/views';
$langFile = __DIR__ . '/lang/th.json';

$existing = file_exists($langFile) ? json_decode(file_get_contents($langFile), true) : [];
if (!is_array($existing)) $existing = [];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$strings = [];

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $content = file_get_contents($file->getPathname());

    // Match __('String') or __("String")
    preg_match_all("/__\(\s*['\"](.*?)['\"]\s*\)/u", $content, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $str) {
            $strings[$str] = $str;
        }
    }
}

$missing = [];
foreach ($strings as $str) {
    if (!isset($existing[$str])) {
        $missing[$str] = "";
    }
}

echo "Found " . count($strings) . " unique translation strings.\n";
echo "Missing " . count($missing) . " translations in th.json.\n";

file_put_contents('missing_th.json', json_encode($missing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Missing strings saved to missing_th.json\n";
