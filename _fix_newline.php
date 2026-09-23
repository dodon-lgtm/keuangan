<?php
/**
 * SEMENTARA — fix newline & tambah tipContainer() di index.blade.php.
 */

$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// 1. Fix: tambah newline antara tipTitle dan tipRow
$old1 = '}function tipRow(label, value, color) {';
$new1 = "}\n\nfunction tipRow(label, value, color) {";
if (strpos($content, $old1) !== false) {
    $content = str_replace($old1, $new1, $content);
    echo "Fixed: newline between tipTitle and tipRow\n";
} else {
    echo "WARNING: pattern 1 not found\n";
}

// 2. Fix: tambah newline antara tipRow dan comment
$old2 = '}// Palet warna per tema';
$new2 = "}\n\n// Palet warna per tema";
if (strpos($content, $old2) !== false) {
    $content = str_replace($old2, $new2, $content);
    echo "Fixed: newline between tipRow and comment\n";
} else {
    echo "WARNING: pattern 2 not found\n";
}

// 3. Tambah tipContainer() setelah fungsi tipRow, sebelum comment
$tipRowEnd = '                }';
$commentStart = '// Palet warna per tema';

$tipRowEndPos = strrpos($content, $tipRowEnd);
$commentPos = strpos($content, $commentStart, $tipRowEndPos);

if ($tipRowEndPos !== false && $commentPos !== false) {
    $tipContainerFunc = "\n\n                function tipContainer() {\n                    var bg = chartPalette.tooltipTheme === 'light' ? 'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)' : 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)';\n                    return '<div style=\"' + bg + '\">';\n                }";

    $content = substr($content, 0, $commentPos) . $tipContainerFunc . substr($content, $commentPos);
    echo "Added: tipContainer() function\n";
} else {
    echo "WARNING: could not find insertion point for tipContainer\n";
}

file_put_contents($path, $content);
echo "Done.\n";
