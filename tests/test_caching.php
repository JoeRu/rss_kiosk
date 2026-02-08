<?php
/**
 * Item 3: File-based caching tests
 * Tests cache validity, read/write cycle, force-refresh bypass,
 * and cache directory auto-creation.
 */

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../api/FeedService.php';

echo "\n=== Caching Tests ===\n\n";

/**
 * Create a test config with an isolated temp cache directory.
 */
function makeTestConfig(bool $cacheEnabled = true, int $ttl = 300): array {
    $tempDir = sys_get_temp_dir() . '/rss_kiosk_test_' . uniqid() . '/';
    return [
        'refresh_interval' => 300,
        'feeds' => [],
        'cache' => [
            'enabled' => $cacheEnabled,
            'ttl' => $ttl,
            'path' => $tempDir
        ],
        'display' => [
            'max_items' => 50,
            'shuffle' => false,
            'exclude_keywords' => []
        ]
    ];
}

/**
 * Clean up a test cache directory.
 */
function cleanupTestDir(string $path): void {
    $files = glob($path . '*');
    if ($files) {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
    @rmdir($path);
}

// --- Cache directory auto-creation ---

test('Constructor creates cache directory if it does not exist', function () {
    $config = makeTestConfig();
    $dir = $config['cache']['path'];

    assert_false(is_dir($dir), 'Dir should not exist before constructor');
    $service = new FeedService($config);
    assert_true(is_dir($dir), 'Dir should exist after constructor');

    cleanupTestDir($dir);
});

// --- Cache status when empty ---

test('getCacheStatus returns uncached when no cache file', function () {
    $config = makeTestConfig();
    $service = new FeedService($config);

    $status = $service->getCacheStatus();
    assert_false($status['cached']);
    assert_eq(0, $status['age']);
    assert_eq(0, $status['nextRefresh']);

    cleanupTestDir($config['cache']['path']);
});

// --- Cache write and read cycle ---

test('getNews caches results and reads from cache', function () {
    $config = makeTestConfig(true, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // First call with empty feeds returns empty array and caches it
    $news = $service->getNews(false);
    assert_eq([], $news);
    assert_true(file_exists($cacheFile), 'Cache file should be created');

    // Verify cached content is valid JSON
    $cached = json_decode(file_get_contents($cacheFile), true);
    assert_eq([], $cached);

    cleanupTestDir($config['cache']['path']);
});

test('getCacheStatus returns cached after write', function () {
    $config = makeTestConfig(true, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // Write cache
    $service->getNews(false);

    $status = $service->getCacheStatus();
    assert_true($status['cached']);
    assert_true($status['age'] >= 0, 'Age should be non-negative');
    assert_true($status['nextRefresh'] > 0, 'Next refresh should be positive');
    assert_true($status['nextRefresh'] <= 300, 'Next refresh should be within TTL');

    cleanupTestDir($config['cache']['path']);
});

// --- Cache TTL validation ---

test('Cache is valid within TTL', function () {
    $config = makeTestConfig(true, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // Write some data to cache
    file_put_contents($cacheFile, json_encode(['test' => true]));

    // Cache should be valid (just created, well within 300s TTL)
    $status = $service->getCacheStatus();
    assert_true($status['cached']);
    assert_true($status['age'] < 300);

    cleanupTestDir($config['cache']['path']);
});

test('Cache is invalid after TTL expires', function () {
    $config = makeTestConfig(true, 1); // 1 second TTL
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // Write cache and backdate it
    file_put_contents($cacheFile, json_encode([]));
    touch($cacheFile, time() - 10); // 10 seconds ago

    $status = $service->getCacheStatus();
    assert_true($status['cached']); // file exists
    assert_true($status['age'] >= 10, 'Age should be >= 10');
    assert_eq(0, $status['nextRefresh'], 'Next refresh should be 0 (expired)');

    cleanupTestDir($config['cache']['path']);
});

// --- Force refresh bypasses cache ---

test('Force refresh fetches fresh data even with valid cache', function () {
    $config = makeTestConfig(true, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // Pre-populate cache with known data
    $oldData = [['title' => 'Old cached item']];
    file_put_contents($cacheFile, json_encode($oldData));

    // Normal request should return cached data
    $news = $service->getNews(false);
    assert_eq($oldData, $news);

    // Force refresh should fetch fresh (empty since no feeds configured)
    $freshNews = $service->getNews(true);
    assert_eq([], $freshNews);

    // Cache should now contain the fresh data
    $cached = json_decode(file_get_contents($cacheFile), true);
    assert_eq([], $cached);

    cleanupTestDir($config['cache']['path']);
});

// --- Cache disabled ---

test('No cache file created when cache is disabled', function () {
    $config = makeTestConfig(false, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    $news = $service->getNews(false);
    assert_false(file_exists($cacheFile), 'No cache file when disabled');

    cleanupTestDir($config['cache']['path']);
});

// --- Cache with actual data structure ---

test('Cached news preserves data structure', function () {
    $config = makeTestConfig(true, 300);
    $service = new FeedService($config);
    $cacheFile = $config['cache']['path'] . 'feed_cache.json';

    // Simulate cached news with full structure
    $newsData = [
        [
            'title' => 'Test Article',
            'timestamp' => '8.2.2026 12:00',
            'timestampUnix' => 1770681600,
            'content' => 'Test content.',
            'images' => ['https://example.com/img.jpg'],
            'link' => 'https://example.com/article',
            'qrcode' => 'data:image/png;base64,abc'
        ]
    ];
    file_put_contents($cacheFile, json_encode($newsData));

    $cached = $service->getNews(false);
    assert_count(1, $cached);
    assert_eq('Test Article', $cached[0]['title']);
    assert_eq('Test content.', $cached[0]['content']);
    assert_count(1, $cached[0]['images']);
    assert_eq('https://example.com/article', $cached[0]['link']);

    cleanupTestDir($config['cache']['path']);
});

$exitCode = test_summary();
exit($exitCode);
