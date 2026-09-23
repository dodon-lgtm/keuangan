<?php
/**
 * SEMENTARA — investigasi area chart 2.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$c = file_get_contents($path);

// Cari wrapper chart 2 (yang kedua)
$first = strpos($c, 'return \'<div style="background:#0F172A');
$second = strpos($c, 'return \'<div style="background:#0F172A', $first + 1);
echo "Wrapper chart 1: pos $first\n";
echo "Wrapper chart 2: pos $second\n";

// Cek area sebelum wrapper chart 2 (tempat yaxis chart 2 seharusnya)
if ($second) {
    echo "\n--- 500 char sebelum wrapper chart 2 ---\n";
    echo substr($c, $second - 500, 500) . "\n";
    
    echo "\n--- 200 char mulai wrapper chart 2 ---\n";
    echo substr($c, $second, 200) . "\n";
}

// Cari 'yaxis:' setelah pos 25000 (area chart 2)
$y2 = strpos($c, 'yaxis:', 25000);
echo "\nyaxis setelah pos 25000: " . ($y2 ?: "TIDAK ADA") . "\n";
if ($y2) {
    echo substr($c, $y2, 200) . "\n";
}

// Cari pola 'opposite: true' yang khas untuk yaxis dual
$opp = strpos($c, 'opposite: true');
echo "\n'opposite: true' pertama: " . ($opp ?: "TIDAK ADA") . "\n";
if ($opp) {
    echo substr($c, $opp - 100, 200) . "\n";
}
