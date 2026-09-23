<?php
/**
 * SEMENTARA — cek apakah semua tooltip wrapper sudah fix.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

$positions = [];
$pos = 0;
while (($pos = strpos($content, 'return tipContainer()', $pos)) !== false) {
    $positions[] = $pos;
    $pos++;
}

echo "tipContainer return positions: " . implode(', ', $positions) . "\n";
foreach ($positions as $p) {
    echo substr($content, $p - 10, 100) . "...\n\n";
}

// Cek apakah ada string yang terputus (return tipContainer() diikuti newline lalu +)
$fragile = preg_match('/return tipContainer\(\)\s*\+\s*[\'"]/s', $content);
echo "Ada syntax fragile (return tipContainer() + string)? " . ($fragile ? "YA - PERLU FIX!" : "TIDAK") . "\n";

// Cek syntax JavaScript dengan node
$scriptMatch = [];
preg_match_all('#<script[^>]*>(.*?)</script>#is', $content, $scriptMatch);
foreach ($scriptMatch[1] as $i => $js) {
    if (trim($js) === '') continue;
    $file = sys_get_temp_dir() . "/check_js_$i.js";
    file_put_contents($file, $js);
    $cmd = 'node --check ' . escapeshellarg($file) . ' 2>&1';
    $out = [];
    $ret = 0;
    exec($cmd, $out, $ret);
    if ($ret !== 0) {
        echo "JS #$i SYNTAX ERROR:\n";
        echo implode("\n", $out) . "\n";
    } else {
        echo "JS #$i: OK\n";
    }
}
