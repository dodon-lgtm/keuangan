<?php
/**
 * SEMENTARA — fix final menyeluruh index.blade.php.
 * 1. Fix tooltip wrapper chart 1 & 2: pakai tipContainer()
 * 2. Fix yaxis chart 1 & 2: chartPalette.axisLabelColor
 * 3. Fix syncChartTheme: tambah yaxis update
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// 1. TOOLTIP WRAPPER — ganti kedua hardcoded wrapper pakai tipContainer()
$oldWrapper = 'return \'<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">\'';
$newWrapper = "return tipContainer() + '";
$count = 0;
while (strpos($content, $oldWrapper) !== false) {
    $content = str_replace($oldWrapper, $newWrapper, $content);
    $count++;
}
echo "OK: tooltip wrapper fixed ($count kali)\n";

// 2. YAXIS CHART 1 — fix hardcoded '#94A3B8'
$oldY1 = 'yaxis: [
                            { labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: \'#94A3B8\', fontFamily: "\'Inter\', sans-serif" } } },
                            { opposite: true, labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: \'#94A3B8\', fontFamily: "\'Inter\', sans-serif" } } }
                        ],';
$newY1 = 'yaxis: [
                            { labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "\'Inter\', sans-serif" } } },
                            { opposite: true, labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "\'Inter\', sans-serif" } } }
                        ],';
if (strpos($content, $oldY1) !== false) {
    $content = str_replace($oldY1, $newY1, $content);
    echo "OK: yaxis chart 1 fixed\n";
} else {
    echo "WARNING: yaxis chart 1 pattern not found\n";
    // Coba tambah debug
    $pos = strpos($content, 'yaxis: [');
    if ($pos !== false) {
        echo "yaxis ditemukan di pos $pos\n";
        echo "Konten 300 char: " . substr($content, $pos, 300) . "\n";
    }
}

// 3. YAXIS CHART 2 — fix hardcoded '#94A3B8'
// Pola sama seperti chart 1, tapi konteksnya setelah chart 2 area
// Kita ganti semua instance yaxis dengan pola yang sama
$oldY2 = 'labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: \'#94A3B8\', fontFamily: "\'Inter\', sans-serif" } }';
$newY2 = 'labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "\'Inter\', sans-serif" } }';
$content = str_replace($oldY2, $newY2, $content);
echo "OK: yaxis chart 2 (pct) fixed\n";

$oldY2b = 'labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: \'#94A3B8\', fontFamily: "\'Inter\', sans-serif" } }';
$newY2b = 'labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "\'Inter\', sans-serif" } }';
$content = str_replace($oldY2b, $newY2b, $content);
echo "OK: yaxis chart 2 (rupiah) fixed\n";

// 4. SYNCCARTTHEME — tambah yaxis update
$oldSync = 'chart.updateOptions({
                            xaxis: { labels: { style: { colors: next.axisLabelColor } } },
                            grid: { borderColor: next.gridBorder, strokeDashArray: next.gridDash },
                            legend: { labels: { colors: next.legendColor } },
                            tooltip: { theme: next.tooltipTheme }
                        });';
$newSync = 'chart.updateOptions({
                            xaxis: { labels: { style: { colors: next.axisLabelColor } } },
                            yaxis: { labels: { style: { colors: next.axisLabelColor } } },
                            grid: { borderColor: next.gridBorder, strokeDashArray: next.gridDash },
                            legend: { labels: { colors: next.legendColor } },
                            tooltip: { theme: next.tooltipTheme }
                        });';
if (strpos($content, $oldSync) !== false) {
    $content = str_replace($oldSync, $newSync, $content);
    echo "OK: syncChartTheme updated\n";
} else {
    echo "WARNING: syncChartTheme pattern not found\n";
}

// Cek keseimbangan kurung
$open = substr_count($content, '{');
$close = substr_count($content, '}');
echo "Braces: $open / $close" . ($open === $close ? " OK" : " MISMATCH!") . "\n";

// Cek literal \n
echo "literal \\n remaining: " . substr_count($content, '\\n') . "\n";

file_put_contents($path, $content);
echo "All fixes saved.\n";
