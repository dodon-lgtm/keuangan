<?php
/**
 * SEMENTARA — investigasi yaxis.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$c = file_get_contents($path);

$y1 = strpos($c, 'yaxis: [', 486);
echo "yaxis1 pos: $y1\n";
if ($y1 !== false) {
    echo "--- isi yaxis chart 1 (250 char) ---\n";
    echo substr($c, $y1, 250) . "\n";
}

$y2 = strpos($c, 'yaxis: [', $y1 + 10);
echo "yaxis2 pos: " . ($y2 ?: "TIDAK ADA") . "\n";
if ($y2) {
    echo "--- isi yaxis chart 2 (250 char) ---\n";
    echo substr($c, $y2, 250) . "\n";
}

// Cek apakah chart 2 punya yaxis dengan pola yang berbeda
echo "\n--- cari 'yaxis:' di seluruh file ---\n";
$positions = [];
$pos = 0;
while (($pos = strpos($c, 'yaxis:', $pos)) !== false) {
    $positions[] = $pos;
    $pos++;
}
echo "Ditemukan " . count($positions) . " instance 'yaxis:'\n";
foreach ($positions as $p) {
    echo "  pos $p: " . substr($c, $p, 80) . "...\n";
}
