<?php
/**
 * SEMENTARA — replace tipTitle & tipRow di index.blade.php agar theme-aware.
 * Pendekatan: replace berdasarkan posisi byte.
 */

$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

$tipTitleStart = strpos($content, 'function tipTitle(text)');
$tipRowStart   = strpos($content, 'function tipRow(label, value, color)');
$commentStart  = strpos($content, '// Palet warna per tema', $tipRowStart);

$tipTitleBlock = substr($content, $tipTitleStart, $tipRowStart - $tipTitleStart);
$tipRowBlock   = substr($content, $tipRowStart, $commentStart - $tipRowStart);

$newTipTitle = <<<'FUNC'
function tipTitle(text) {
                    var bg = chartPalette.tooltipTheme === 'light' ? 'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)' : 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)';
                    var borderColor = chartPalette.tooltipTheme === 'light' ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.14)';
                    return '<div style="' + bg + ';border-bottom:1px solid ' + borderColor + ';font-weight:700;margin-bottom:6px;padding-bottom:4px;font-size:12px">'
                        + text
                        + '</div>';
                }
FUNC;

$newTipRow = <<<'FUNC'
function tipRow(label, value, color) {
                    var dot = color ? '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';margin-right:6px"></span>' : '';
                    var textColor = chartPalette.tooltipTheme === 'light' ? '#14161A' : '#F8FAFC';
                    return '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.8;color:' + textColor + ';font-size:12px">'
                        + '<span>' + dot + label + '</span>'
                        + '<strong style="font-variant-numeric:tabular-nums;color:' + textColor + '">' + value + '</strong>'
                        + '</div>';
                }
FUNC;

$newContent = substr($content, 0, $tipTitleStart)
    . $newTipTitle
    . substr($content, $tipRowStart, $commentStart - $tipRowStart)  // = $newTipRow
    . substr($content, $commentStart);

// Tapi need to replace tipRow block too. Let me redo:
$newContent = substr($content, 0, $tipTitleStart)
    . $newTipTitle
    . $newTipRow
    . substr($content, $commentStart);

file_put_contents($path, $newContent);
echo "OK: tipTitle & tipRow replaced\n";
echo "tipTitle pos: $tipTitleStart\n";
echo "tipRow pos: $tipRowStart\n";
echo "comment pos: $commentStart\n";
