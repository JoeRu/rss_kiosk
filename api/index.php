<?php
/**
 * RSS Feed REST API
 * Endpoints for fetching news and configuration
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/FeedService.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize service
$feedService = new FeedService($config);

// Get request path
$path = $_SERVER['PATH_INFO'] ?? $_GET['path'] ?? '/news';

try {
    switch ($path) {
        case '/news':
            // Get news items
            $forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === 'true';
            $news = $feedService->getNews($forceRefresh);
            
            echo json_encode([
                'success' => true,
                'data' => $news,
                'count' => count($news),
                'timestamp' => time()
            ]);
            break;
            
        case '/config':
            // Get public configuration
            echo json_encode([
                'success' => true,
                'data' => [
                    'refreshInterval' => $config['refresh_interval'],
                    'maxItems' => $config['display']['max_items'],
                    'feedCount' => count($config['feeds'])
                ]
            ]);
            break;
            
        case '/status':
            // Get cache status
            $status = $feedService->getCacheStatus();
            echo json_encode([
                'success' => true,
                'data' => $status
            ]);
            break;
            
        case '/refresh':
            // Force refresh
            $news = $feedService->getNews(true);
            echo json_encode([
                'success' => true,
                'message' => 'Feed refreshed successfully',
                'data' => $news,
                'count' => count($news)
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Endpoint not found',
                'availableEndpoints' => ['/news', '/config', '/status', '/refresh']
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
