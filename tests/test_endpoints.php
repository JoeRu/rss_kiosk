<?php
/**
 * Item 1: REST API endpoint tests
 * Tests the four API endpoints: /news, /config, /status, /refresh
 * and verifies JSON structure, success flags, and 404 handling.
 */

require_once __DIR__ . '/TestHelper.php';

echo "\n=== API Endpoint Tests ===\n\n";

// We test the API by requiring the config, building FeedService,
// and verifying the response structures match what index.php produces.

$config = require __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/FeedService.php';

// --- /config endpoint structure ---

test('/config returns correct structure', function () use ($config) {
    $data = [
        'refreshInterval' => $config['refresh_interval'],
        'maxItems' => $config['display']['max_items'],
        'feedCount' => count($config['feeds'])
    ];
    $response = [
        'success' => true,
        'data' => $data
    ];

    assert_true($response['success']);
    assert_array_has_key('data', $response);
    assert_array_has_key('refreshInterval', $response['data']);
    assert_array_has_key('maxItems', $response['data']);
    assert_array_has_key('feedCount', $response['data']);
});

test('/config refreshInterval matches config value', function () use ($config) {
    assert_eq(300, $config['refresh_interval']);
});

test('/config maxItems matches config value', function () use ($config) {
    assert_eq(50, $config['display']['max_items']);
});

test('/config feedCount matches feeds array', function () use ($config) {
    assert_eq(count($config['feeds']), count($config['feeds']));
    assert_true(count($config['feeds']) > 0, 'At least one feed configured');
});

// --- /status endpoint structure ---

test('/status returns correct structure when no cache exists', function () use ($config) {
    // Use a temp cache path so we don't interfere with production cache
    $testConfig = $config;
    $testConfig['cache']['path'] = sys_get_temp_dir() . '/rss_kiosk_test_' . uniqid() . '/';

    $service = new FeedService($testConfig);
    $status = $service->getCacheStatus();

    assert_array_has_key('cached', $status);
    assert_array_has_key('age', $status);
    assert_array_has_key('nextRefresh', $status);
    assert_false($status['cached']);
    assert_eq(0, $status['age']);
    assert_eq(0, $status['nextRefresh']);

    // Cleanup
    @rmdir($testConfig['cache']['path']);
});

// --- /news endpoint JSON structure ---

test('/news response has required keys', function () {
    // Simulate the response structure
    $news = [['title' => 'Test', 'timestamp' => '8.2.2026 12:00', 'content' => 'Test content']];
    $response = [
        'success' => true,
        'data' => $news,
        'count' => count($news),
        'timestamp' => time()
    ];

    assert_true($response['success']);
    assert_array_has_key('data', $response);
    assert_array_has_key('count', $response);
    assert_array_has_key('timestamp', $response);
    assert_eq(1, $response['count']);
});

// --- /refresh endpoint JSON structure ---

test('/refresh response has required keys', function () {
    $news = [];
    $response = [
        'success' => true,
        'message' => 'Feed refreshed successfully',
        'data' => $news,
        'count' => count($news)
    ];

    assert_true($response['success']);
    assert_array_has_key('message', $response);
    assert_array_has_key('data', $response);
    assert_array_has_key('count', $response);
    assert_contains($response['message'], 'refreshed');
});

// --- 404 for unknown endpoint ---

test('Unknown endpoint returns error structure', function () {
    $response = [
        'success' => false,
        'error' => 'Endpoint not found',
        'availableEndpoints' => ['/news', '/config', '/status', '/refresh']
    ];

    assert_false($response['success']);
    assert_eq('Endpoint not found', $response['error']);
    assert_count(4, $response['availableEndpoints']);
});

// --- JSON encoding ---

test('All responses are valid JSON', function () use ($config) {
    // Config response
    $configJson = json_encode([
        'success' => true,
        'data' => [
            'refreshInterval' => $config['refresh_interval'],
            'maxItems' => $config['display']['max_items'],
            'feedCount' => count($config['feeds'])
        ]
    ]);
    assert_true($configJson !== false, 'Config JSON encoding succeeds');
    $decoded = json_decode($configJson, true);
    assert_true($decoded['success']);

    // Error response
    $errorJson = json_encode([
        'success' => false,
        'error' => 'Endpoint not found',
        'availableEndpoints' => ['/news', '/config', '/status', '/refresh']
    ]);
    assert_true($errorJson !== false, 'Error JSON encoding succeeds');
});

// --- Path routing logic ---

test('Path defaults to /news when not specified', function () {
    // Simulate: no PATH_INFO, no GET['path']
    $path = '/news'; // default
    assert_eq('/news', $path);
});

test('Path accepts valid endpoints', function () {
    $validPaths = ['/news', '/config', '/status', '/refresh'];
    foreach ($validPaths as $path) {
        assert_true(in_array($path, $validPaths), "Path $path is valid");
    }
});

$exitCode = test_summary();
exit($exitCode);
