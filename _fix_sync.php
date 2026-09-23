<?php
/**
 * SEMENTARA — fix syncChartTheme: tambah yaxis update.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

$syncPos = strpos($content, 'chart.updateOptions({');
if ($syncPos !== false) {
    // Cari akhir blok: 이時は })\\n (tanpa semicolon)
    $syncEnd = strpos($content, "})", $syncPos);
    if ($syncEnd !== false) {
        // Include the closing }) and the following newline
        $syncEndWithNewline = strpos($content, "\n", $syncEnd);
        if ($syncEndWithNewline !== false) {
            $syncBlock = substr($content, $syncPos, $syncEndWithNewline - $syncPos);
        } else {
            $syncBlock = substr($content, $syncPos, $syncEnd - $syncPos + 2);
        }
        echo "syncChartTheme block:\n$syncBlock\n";
        
        // Tambah yaxis line sebelum grid line
        $newSyncBlock = str_replace(
            "xaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            grid:",
            "xaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            yaxis: { labels: { style: { colors: next.axisLabelColor } } },\n                            grid:"
        );
        if ($newSyncBlock !== $syncBlock) {
            $content = substr($content, 0, $syncPos) . $newSyncBlock . substr($content, $syncPos + strlen($syncBlock));
            echo "OK: syncChartTheme updated (yaxis added)\n";
        } else {
            echo "WARNING: replacement didn't change anything\n";
        }
    } else {
        echo "WARNING: syncChartTheme end (}) not found\n";
    }
} else {
    echo "WARNING: chart.updateOptions not found\n";
}

$open = substr_count($content, '{');
$close = substr_count($content, '}');
echo "Braces: $open / $close" . ($open === $close ? " OK" : " MISMATCH!") . "\n";

file_put_contents($path, $content);
echo "Saved.\n";
