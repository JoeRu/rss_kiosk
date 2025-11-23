<?php
/**
 * RSS Feed Configuration
 * Configure your RSS feeds and refresh interval here
 */

return [
    // Refresh interval in seconds (default: 300 = 5 minutes)
    'refresh_interval' => 300,
    
    // RSS feed URLs to fetch
    'feeds' => [
        'https://www.hr3.de/index.rss',
        'https://www.tagesschau.de/xml/rss2',
        'https://rss.sueddeutsche.de/rss/Alles'
    ],
    
    // Cache settings
    'cache' => [
        'enabled' => true,
        'ttl' => 300, // Cache time-to-live in seconds
        'path' => __DIR__ . '/../cache/'
    ],
    
    // Display settings
    'display' => [
        'max_items' => 50, // Maximum number of news items to return
        'shuffle' => true, // Shuffle news items
        'exclude_keywords' => ['hr3 app', 'hr3 skill'] // Filter out items containing these keywords
    ]
];
