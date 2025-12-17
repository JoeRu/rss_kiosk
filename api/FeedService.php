<?php
/**
 * Feed Service
 * Handles RSS feed fetching and processing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;

class FeedService {
    private $config;
    private $cacheFile;
    
    public function __construct($config) {
        $this->config = $config;
        $this->cacheFile = $config['cache']['path'] . 'feed_cache.json';
        
        // Ensure cache directory exists
        if (!is_dir($config['cache']['path'])) {
            mkdir($config['cache']['path'], 0755, true);
        }
        
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('Europe/Berlin');
        }
    }
    
    /**
     * Get news items from RSS feeds
     */
    public function getNews($forceRefresh = false) {
       //  error_log("[FeedService] getNews called, forceRefresh: " . ($forceRefresh ? 'true' : 'false'));
        
        // Check cache first
        if (!$forceRefresh && $this->config['cache']['enabled'] && $this->isCacheValid()) {
           //  error_log("[FeedService] Using cached news");
            return $this->getCachedNews();
        }
        
       //  error_log("[FeedService] Fetching fresh news from feeds");
        // Fetch fresh news
        $news = $this->fetchFeeds();
        
        // Cache the results
        if ($this->config['cache']['enabled']) {
            $this->cacheNews($news);
        }
        
        return $news;
    }
    
    /**
     * Fetch and process RSS feeds
     */
    private function fetchFeeds() {
        require_once __DIR__ . '/../src/Feed.php';
        
       //  error_log("[FeedService] Loading " . count($this->config['feeds']) . " feeds");
        $rss_feeds = [];
        foreach ($this->config['feeds'] as $feedUrl) {
            try {
               //  error_log("[FeedService] Loading feed: $feedUrl");
                $rss_feeds[] = Feed::loadRss($feedUrl);
               //  error_log("[FeedService] Successfully loaded: $feedUrl");
            } catch (Exception $e) {
               //  error_log("[FeedService] Failed to load feed: $feedUrl - " . $e->getMessage());
            }
        }
        
       //  error_log("[FeedService] Processing " . count($rss_feeds) . " loaded feeds");
        $nachrichten = [];
        foreach ($rss_feeds as $feed) {
            // Try RSS format first (item)
            $itemCount = 0;
            if ($feed->item && count($feed->item) > 0) {
                $itemCount = count($feed->item);
               //  error_log("[FeedService] RSS Feed has $itemCount items");
                foreach ($feed->item as $item) {
                    $nachrichten[] = $item;
                }
            }
            // Try Atom format (entry)
            elseif ($feed->entry && count($feed->entry) > 0) {
                $itemCount = count($feed->entry);
               //  error_log("[FeedService] Atom Feed has $itemCount entries");
                foreach ($feed->entry as $item) {
                    $nachrichten[] = $item;
                }
            } else {
               //  error_log("[FeedService] Feed has no items or entries");
            }
        }
        
       //  error_log("[FeedService] Processing " . count($nachrichten) . " total items");
        $news = [];
        foreach ($nachrichten as $item) {
            $title = strip_tags($item->title);
            $timestamp = date('j.n.Y H:i', (int) $item->timestamp);
            $timestampUnix = (int) $item->timestamp;
            
            $rawContent = '';
            if (isset($item->{'content:encoded'})) {
                $rawContent = (string) $item->{'content:encoded'};
            } else {
                $rawContent = (string) $item->description;
            }
            
            // Extract images from content
            $images = $this->extractImages($rawContent);
            
            // Clean content and remove images
            $content = $this->cleanContent(strip_tags($rawContent));
            
            // Shorten content if necessary
            $content = $this->shortenContent($content);
            
            $link = isset($item->link) ? (string) $item->link : '';
            
            // Filter out unwanted items
            if ($title != '' && !$this->shouldExclude($title)) {
                $news[] = [
                    'title' => $title,
                    'timestamp' => $timestamp,
                    'timestampUnix' => $timestampUnix,
                    'content' => $content,
                    'images' => $images,
                    'link' => $link,
                    'qrcode' => $link ? $this->generateQRCode($link) : null
                ];
            }
        }
        
        // Limit number of items
        if (count($news) > $this->config['display']['max_items']) {
            $news = array_slice($news, 0, $this->config['display']['max_items']);
        }
        
        // Shuffle if configured
        if ($this->config['display']['shuffle']) {
            shuffle($news);
        }
        
       //  error_log("[FeedService] Returning " . count($news) . " news items");
        return $news;
    }
    
    /**
     * Clean content by removing unwanted text
     */
    private function cleanContent($content) {
        $content = trim(preg_replace('/\s+/', ' ', $content));
        $content = preg_replace('/(.*)\[mehr\].*/i', '$1', $content);
        $content = str_replace(" mehr Meldung bei www.tagesschau.de lesen", "", $content);
        return $content;
    }
    
    /**
     * Extract images from content
     * Returns array of image URLs
     */
    private function extractImages($content) {
        $images = [];
        
        // Match all img tags and extract src attributes
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            foreach ($matches[1] as $imageSrc) {
                // Clean and validate URL
                $imageSrc = trim($imageSrc);
                if (!empty($imageSrc) && (strpos($imageSrc, 'http') === 0 || strpos($imageSrc, '//') === 0)) {
                    $images[] = $imageSrc;
                }
            }
        }
        
        // Remove duplicates
        $images = array_unique($images);
        
        return array_values($images);
    }
    
    /**
     * Shorten content to max 2 sentences or 70 characters
     */
    private function shortenContent($content) {
        // Remove extra whitespace
        $content = trim(preg_replace('/\s+/', ' ', $content));
        
        if (empty($content)) {
            return $content;
        }
        
        // Split into sentences (looking for . ! ? followed by space or end)
        preg_match_all('/[^.!?]+[.!?]+/', $content, $sentences);
        $sentences = $sentences[0];
        
        // If no sentence endings found, treat whole content as one sentence
        if (empty($sentences)) {
            $sentences = [$content];
        }
        
        // Count sentences and check length
        $sentenceCount = count($sentences);
        $totalLength = strlen($content);
        
        // If more than 2 sentences OR more than 70 characters, shorten it
        if ($sentenceCount > 2 || $totalLength > 70) {
            // Take first sentence
            $shortened = trim($sentences[0]);
            
            // If first sentence is very short and we have a second, include it
            if (strlen($shortened) < 40 && isset($sentences[1])) {
                $shortened .= ' ' . trim($sentences[1]);
            }
            
            return $shortened;
        }
        
        return $content;
    }
    
    /**
     * Check if title should be excluded based on keywords
     */
    private function shouldExclude($title) {
        foreach ($this->config['display']['exclude_keywords'] as $keyword) {
            if (stripos($title, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Generate QR code for a URL
     */
    private function generateQRCode($url) {
        try {
            return (new QRCode)->render($url);
        } catch (Exception $e) {
           //  error_log("Failed to generate QR code: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if cached news is still valid
     */
    private function isCacheValid() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cacheAge = time() - filemtime($this->cacheFile);
        return $cacheAge < $this->config['cache']['ttl'];
    }
    
    /**
     * Get cached news
     */
    private function getCachedNews() {
        $content = file_get_contents($this->cacheFile);
        return json_decode($content, true);
    }
    
    /**
     * Cache news items
     */
    private function cacheNews($news) {
        try {
            $result = @file_put_contents($this->cacheFile, json_encode($news));
            if ($result === false) {
               //  error_log("Failed to write cache file: " . $this->cacheFile);
            }
        } catch (Exception $e) {
           //  error_log("Cache write error: " . $e->getMessage());
        }
    }
    
    /**
     * Get cache status
     */
    public function getCacheStatus() {
        if (!file_exists($this->cacheFile)) {
            return [
                'cached' => false,
                'age' => 0,
                'nextRefresh' => 0
            ];
        }
        
        $age = time() - filemtime($this->cacheFile);
        $ttl = $this->config['cache']['ttl'];
        
        return [
            'cached' => true,
            'age' => $age,
            'nextRefresh' => max(0, $ttl - $age)
        ];
    }
}
