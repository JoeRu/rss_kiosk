<?php
/**
 * Minimal test helper – no external dependencies.
 * Usage:
 *   require_once __DIR__ . '/TestHelper.php';
 *   test('description', function () { assert_eq(1, 1); });
 *   test_summary();
 */

$_test_results = ['pass' => 0, 'fail' => 0, 'errors' => []];

function test(string $name, callable $fn): void {
    global $_test_results;
    try {
        $fn();
        $_test_results['pass']++;
        echo "  PASS  $name\n";
    } catch (Throwable $e) {
        $_test_results['fail']++;
        $_test_results['errors'][] = "$name: " . $e->getMessage();
        echo "  FAIL  $name\n        " . $e->getMessage() . "\n";
    }
}

function assert_eq($expected, $actual, string $msg = ''): void {
    if ($expected !== $actual) {
        $exp = var_export($expected, true);
        $act = var_export($actual, true);
        throw new RuntimeException(($msg ? "$msg: " : '') . "expected $exp, got $act");
    }
}

function assert_true($value, string $msg = ''): void {
    if ($value !== true) {
        throw new RuntimeException(($msg ? "$msg: " : '') . "expected true, got " . var_export($value, true));
    }
}

function assert_false($value, string $msg = ''): void {
    if ($value !== false) {
        throw new RuntimeException(($msg ? "$msg: " : '') . "expected false, got " . var_export($value, true));
    }
}

function assert_contains(string $haystack, string $needle, string $msg = ''): void {
    if (strpos($haystack, $needle) === false) {
        throw new RuntimeException(($msg ? "$msg: " : '') . "string does not contain '$needle'");
    }
}

function assert_count(int $expected, $array, string $msg = ''): void {
    $actual = count($array);
    if ($expected !== $actual) {
        throw new RuntimeException(($msg ? "$msg: " : '') . "expected count $expected, got $actual");
    }
}

function assert_array_has_key($key, array $array, string $msg = ''): void {
    if (!array_key_exists($key, $array)) {
        throw new RuntimeException(($msg ? "$msg: " : '') . "array does not have key '$key'");
    }
}

function test_summary(): int {
    global $_test_results;
    $total = $_test_results['pass'] + $_test_results['fail'];
    echo "\n  Results: {$_test_results['pass']}/$total passed";
    if ($_test_results['fail'] > 0) {
        echo " ({$_test_results['fail']} failed)";
    }
    echo "\n";
    return $_test_results['fail'] > 0 ? 1 : 0;
}
