<?php
/**
 * SEMENTARA — investigasi & fix sisa masalah:
 * - tooltip wrapper chart 1 masih hardcode
 * - yaxis chart 1 (kalau ada)
 * - yaxis chart 2 (sudah diproyeksikan hardcode)
 * - syncChartTheme belum yaxis
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// Cek wrapper chart 1
$search = 'return \'<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">\'';
$pos = strpos($content, $search);
echo "Wrapper chart 1 ditemukan di: " . ($pos !== false ? $pos : "TIDAK DITEMUKAN") . "\n";

// Cek wrapper chart 2
$search2 = 'return \'<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">\'';
$pos2 = strrpos($content, $search2);  // terakhir = chart 2
echo "Wrapper chart 2 ditemukan di: " . ($pos2 !== false ? $pos2 : "TIDAK DITEMUKAN") . "\n";

// Cek apakah tipContainer sudah ada
$tipContainer = 'function tipContainer()';
$tcPos = strpos($content, $tipContainer);
echo "tipContainer ada: " . ($tcPos !== false ? "YA di pos $tcPos" : "TIDAK") . "\n";

// Cek yaxis chart 1
$yaxis1 = strpos($content, 'yaxis: [', 486);  // mulai dari area chart 1
echo "yaxis chart 1 pertama setelah chart-trend: " . ($yaxis1 !== false ? "di pos $yaxis1" : "TIDAK DITEMUKAN") . "\n";

// Cek yaxis chart 2
$yaxis2 = strpos($content, 'yaxis: [', $yaxis1 + 10);
echo "yaxis chart 2 pertama: " . ($yaxis2 !== false ? "di pos $yaxis2" : "TIDAK DITEMUKAN") . "\n";

// Cek warna yaxis
if ($yaxis2 !== false) {
    $yaxisBlock = substr($content, $yaxis2, 300);
    $hasHardcoded = strpos($yaxisBlock, "'#94A3B8'") !== false;
    echo "yaxis chart 2 masih hardcode '#94A3B8': " . ($hasHardcoded ? "YA" : "TIDAK") . "\n";
}

// Cek syncChartTheme
$sync = strpos($content, 'function syncChartTheme()');
echo "syncChartTheme ada: " . ($sync !== false ? "YA" : "TIDAK") . "\n";
if ($sync !== false) {
    $syncEnd = strpos($content, "\n                }", $sync + 100);
    $syncBlock = substr($content, $sync, $syncEnd - $sync + 10);
    $hasYaxis = strpos($syncBlock, 'yaxis:') !== false;
    echo "syncChartTheme punya yaxis: " . ($hasYaxis ? "YA" : "TIDAK") . "\n";
}

// Statistik umum
echo "\n--- statistik ---\n";
echo "Panjang file: " . strlen($content) . " bytes\n";
echo "Jumlah '{': " . substr_count($content, '{') . "\n";
echo "Jumlah '}': " . substr_count($content, '}') . "\n";
echo "literal '\\n' count: " . substr_count($content, '\\n') . "\n";
echo "actual newline count: " . substr_count($content, "\n") . "\n";
