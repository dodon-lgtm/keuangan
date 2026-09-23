<?php
/**
 * SEMENTARA — fix robust: yaxis & syncChartTheme.
 * Pendekatan: replace berdasarkan posisi, bukan pattern matching.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// --- YAXIS CHART 1 (pos ~25710) ---
// Cari yaxis pertama setelah area chart 1
$y1Pos = strpos($content, 'yaxis: [', 25000);
if ($y1Pos !== false) {
    echo "yaxis chart 1 ditemukan di pos $y1Pos\n";
    // Cari akhir yaxis block (tutup bracket diikuti koma atau newline)
    $y1End = strpos($content, "],\n", $y1Pos);
    if ($y1End !== false) {
        $y1Block = substr($content, $y1Pos, $y1End - $y1Pos + 2);
        echo "yaxis chart 1 block:\n$y1Block\n";
        
        // Replace hardcoded colors dalam block ini
        $newY1Block = str_replace(
            "'#94A3B8'",
            "chartPalette.axisLabelColor",
            $y1Block
        );
        $content = substr($content, 0, $y1Pos) . $newY1Block . substr($content, $y1End + 2);
        echo "OK: yaxis chart 1 fixed (replace within block)\n";
    } else {
        echo "WARNING: yaxis chart 1 end not found\n";
    }
} else {
    echo "WARNING: yaxis chart 1 not found\n";
}

// --- YAXIS CHART 2 (pos ~26059) ---
$y2Pos = strpos($content, 'yaxis: [', $y1Pos + 10);
if ($y2Pos !== false) {
    echo "yaxis chart 2 ditemukan di pos $y2Pos\n";
    $y2End = strpos($content, "],\n", $y2Pos);
    if ($y2End !== false) {
        $y2Block = substr($content, $y2Pos, $y2End - $y2Pos + 2);
        $newY2Block = str_replace(
            "'#94A3B8'",
            "chartPalette.axisLabelColor",
            $y2Block
        );
        $content = substr($content, 0, $y2Pos) . $newY2Block . substr($content, $y2End + 2);
        echo "OK: yaxis chart 2 fixed (replace within block)\n";
    } else {
        echo "WARNING: yaxis chart 2 end not found\n";
    }
} else {
    echo "WARNING: yaxis chart 2 not found\n";
}

// --- SYNCCARTTHEME ---
$syncPos = strpos($content, 'chart.updateOptions({');
if ($syncPos !== false) {
    // Cari blok updateOptions (dari chart.updateOptions({ sampai });)
    $syncEnd = strpos($content, "});", $syncPos);
    if ($syncEnd !== false) {
        $syncBlock = substr($content, $syncPos, $syncEnd - $syncPos + 2);
        echo "syncChartTheme block:\n$syncBlock\n";
        
        // Tambah yaxis line sebelum grid line
        $newSyncBlock = str_replace(
            "xaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            grid:",
            "xaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            yaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            grid:"
        );
        if ($newSyncBlock !== $syncBlock) {
            $content = substr($content, 0, $syncPos) . $newSyncBlock . substr($content, $syncEnd + 2);
            echo "OK: syncChartTheme updated (yaxis added)\n";
        } else {
            echo "WARNING: syncChartTheme pattern not matched for replacement\n";
        }
    } else {
        echo "WARNING: syncChartTheme end not found\n";
    }
} else {
    echo "WARNING: syncChartTheme not found\n";
}

// Cek keseimbangan
$open = substr_count($content, '{');
$close = substr_count($content, '}');
echo "Braces: $open / $close" . ($open === $close ? " OK" : " MISMATCH!") . "\n";

file_put_contents($path, $content);
echo "Saved.\n";
