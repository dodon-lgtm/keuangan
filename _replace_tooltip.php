<?php
/**
 * SEMENTARA — replace tipTitle & tipRow di index.blade.php agar theme-aware.
 */

$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// --- tipTitle (baris 390-393) ---
$oldTipTitle = '                function tipTitle(text) {
                    return \'<div style="font-weight:700;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid rgba(255,255,255,.14);color:#f8fafc;font-size:12px">\'
                        + text
                        + \'</div>\';
                }';

$newTipTitle = '                function tipTitle(text) {
                    var bg = chartPalette.tooltipTheme === \'light\' ? \'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)\' : \'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)\';
                    var borderColor = chartPalette.tooltipTheme === \'light\' ? \'rgba(0,0,0,0.12)\' : \'rgba(255,255,255,0.14)\';
                    return \'<div style="\' + bg + \';border-bottom:1px solid \' + borderColor + \';font-weight:700;margin-bottom:6px;padding-bottom:4px;font-size:12px">\'
                        + text
                        + \'</div>\';
                }';

// --- tipRow (baris 396-402) ---
$oldTipRow = '                function tipRow(label, value, color) {
                    var dot = color ? \'<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:\' + color + \';margin-right:6px"></span>\' : \'\';
                    return \'<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.8;color:#f8fafc;font-size:12px">\'
                        + \'<span>\' + dot + label + \'</span>\'
                        + \'<strong style="font-variant-numeric:tabular-nums">\' + value + \'</strong>\'
                        + \'</div>\';
                }';

$newTipRow = '                function tipRow(label, value, color) {
                    var dot = color ? \'<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:\' + color + \';margin-right:6px"></span>\' : \'\';
                    var textColor = chartPalette.tooltipTheme === \'light\' ? \'#14161A\' : \'#F8FAFC\';
                    return \'<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.8;color:\' + textColor + \';font-size:12px">\'
                        + \'<span>\' + dot + label + \'</span>\'
                        + \'<strong style="font-variant-numeric:tabular-nums;color:\' + textColor + \'">\' + value + \'</strong>\'
                        + \'</div>\';
                }';

if (strpos($content, $oldTipTitle) === false) {
    echo "ERROR: old tipTitle not found\n";
    exit(1);
}
if (strpos($content, $oldTipRow) === false) {
    echo "ERROR: old tipRow not found\n";
    exit(1);
}

$content = str_replace($oldTipTitle, $newTipTitle, $content);
$content = str_replace($oldTipRow, $newTipRow, $content);

file_put_contents($path, $content);
echo "OK: tipTitle & tipRow replaced\n";
