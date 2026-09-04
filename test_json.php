<?php
// Diagnose whether json_encode works on the campus bloc data
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
$src = file_get_contents('campus.php');
$start = strpos($src, '$campus_blocs = [');
$end = strpos($src, '// Tri des blocs');
if ($start === false || $end === false) {
    echo "Could not locate array in source\n";
    exit(1);
}
$code = substr($src, $start, $end - $start);
try {
    eval($code . ';');
} catch (\Throwable $e) {
    echo "Eval failed: " . $e->getMessage() . "\n";
    exit(1);
}
$json = json_encode($campus_blocs);
if ($json === false) {
    echo "json_encode FAILED: " . json_last_error_msg() . "\n";
    echo "json_last_error code = " . json_last_error() . "\n";
    // Try to find the offending string
    foreach ($campus_blocs as $idx => $b) {
        $t = json_encode($b);
        if ($t === false) {
            echo "Failing block #$idx: " . $b['id'] . " reason: " . json_last_error_msg() . "\n";
        }
    }
    exit(1);
}
echo "json_encode OK. Length=" . strlen($json) . "\n";
$decoded = json_decode($json);
if ($decoded === null) {
    echo "json_decode FAILED: " . json_last_error_msg() . "\n";
} else {
    echo "json_decode OK, count=" . count($decoded) . "\n";
}
// Check for JS-breaking sequences
$checks = [
    '</script>' => 'closing script tag',
    "\u{2028}" => 'U+2028 line separator',
    "\u{2029}" => 'U+2029 paragraph separator',
];
foreach ($checks as $seq => $label) {
    if (strpos($json, $seq) !== false) {
        echo "WARNING: json contains $label\n";
    } else {
        echo "OK: no $label in json\n";
    }
}
?>
