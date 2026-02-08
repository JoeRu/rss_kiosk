<?php
/**
 * Test runner – executes all test files and reports aggregate results.
 * Usage: php tests/run_all.php
 */

$testFiles = glob(__DIR__ . '/test_*.php');
$totalPass = 0;
$totalFail = 0;

foreach ($testFiles as $file) {
    $basename = basename($file);
    $output = [];
    $exitCode = 0;
    exec('php "' . $file . '" 2>&1', $output, $exitCode);

    echo implode("\n", $output) . "\n";

    // Parse results from last line
    $lastLines = array_filter($output, fn($l) => strpos($l, 'Results:') !== false);
    foreach ($lastLines as $line) {
        if (preg_match('/(\d+)\/(\d+) passed/', $line, $m)) {
            $totalPass += (int) $m[1];
            $totalFail += ((int) $m[2] - (int) $m[1]);
        }
    }
}

$total = $totalPass + $totalFail;
echo "\n========================================\n";
echo "TOTAL: {$totalPass}/{$total} passed";
if ($totalFail > 0) {
    echo " ({$totalFail} FAILED)";
}
echo "\n========================================\n";

exit($totalFail > 0 ? 1 : 0);
