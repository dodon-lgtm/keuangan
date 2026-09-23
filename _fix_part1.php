<?php
/**
 * SEMENTARA — fix bagian 1: literal \n, tipContainer, tooltip wrapper, tooltip theme, legend.
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// 1. Ubah literal \n jadi newline
$content = str_replace('\\n', "\n", $content);
echo "OK: literal \\n fixed\n";

// 2. Tambah tipContainer() setelah tipRow, sebelum comment
$tipRowSig = 'function tipRow(label, value, color) {';
$commentSig = '// Palet warna per tema';
$tipRowStart = strpos($content, $tipRowSig);
$commentStart = strpos($content, $commentSig, $tipRowStart);
$tipRowEnd = strpos($content, "\n                }", $tipRowStart);
$tipContainerFunc = "\n\n                function tipContainer() {\n"
    . "                    var bg = chartPalette.tooltipTheme === 'light' ? 'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)' : 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)';\n"
    . "                    return '<div style=\"' + bg + '\">';\n"
    . "                }";
$content = substr($content, 0, $commentStart) . $tipContainerFunc . substr($content, $commentStart);
echo "OK: tipContainer added\n";

// 3a. Fix chart 1 & 2 tooltip wrapper: ganti hardcode pakai tipContainer()
$oldWrapper = 'return \'<div style=\"background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)\">\'';
$newWrapper = "return tipContainer() + '";
$content = str_replace($oldWrapper, $newWrapper, $content);
echo "OK: tooltip wrappers fixed\n";

// 3b. Fix chart 1 tooltip theme: 'dark' -> chartPalette.tooltipTheme
$oldTT1 = "                            theme: 'dark',\n                            custom: function (opts) {\n                                var i = opts.dataPointIndex;\n                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }\n\n                                var omset = opts.series[0][i] || 0;";
$newTT1 = "                            theme: chartPalette.tooltipTheme,\n                            custom: function (opts) {\n                                var i = opts.dataPointIndex;\n                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }\n\n                                var omset = opts.series[0][i] || 0;";
$content = str_replace($oldTT1, $newTT1, $content);
echo "OK: chart 1 tooltip theme fixed\n";

// 3c. Fix chart 1 legend: colors '#94A3B8' -> chartPalette.legendColor
$oldLeg1 = "                        legend: { show: true, position: 'bottom', labels: { colors: '#94A3B8' } }\n                    })).render();";
$newLeg1 = "                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }\n                    })).render();";
$content = str_replace($oldLeg1, $newLeg1, $content);
echo "OK: chart 1 legend fixed\n";

// 3d. Fix chart 2 tooltip theme: 'dark' -> chartPalette.tooltipTheme
$oldTT2 = "                        tooltip: {\n                            theme: 'dark',\n                            custom: function (opts) {\n                                var i = opts.dataPointIndex;\n                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }\n\n                                var spend = opts.series[0][i] || 0;";
$newTT2 = "                        tooltip: {\n                            theme: chartPalette.tooltipTheme,\n                            custom: function (opts) {\n                                var i = opts.dataPointIndex;\n                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }\n\n                                var spend = opts.series[0][i] || 0;";
$content = str_replace($oldTT2, $newTT2, $content);
echo "OK: chart 2 tooltip theme fixed\n";

// 3e. Fix chart 2 legend: colors '#94A3B8' -> chartPalette.legendColor
$oldLeg2 = "                        legend: { show: true, position: 'bottom', labels: { colors: '#94A3B8' } }\n                    })).render();\n                }\n            });\n        </script>";
$newLeg2 = "                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }\n                    })).render();\n                }\n            });\n        </script>";
$content = str_replace($oldLeg2, $newLeg2, $content);
echo "OK: chart 2 legend fixed\n";

file_put_contents($path, $content);
echo "Part 1 saved.\n";
