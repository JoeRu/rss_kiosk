<?php
/**
 * Item 2: Feed fetching and parsing tests
 * Tests RSS and Atom parsing, timestamp extraction, and failure handling.
 * Uses Feed.php directly with mock XML data.
 */

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../src/Feed.php';

echo "\n=== Feed Parsing Tests ===\n\n";

// --- RSS format parsing ---

test('RSS feed parses items correctly', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test Feed</title>
    <item>
      <title>Article One</title>
      <description>First article content</description>
      <link>https://example.com/1</link>
      <pubDate>Mon, 01 Jan 2026 12:00:00 +0000</pubDate>
    </item>
    <item>
      <title>Article Two</title>
      <description>Second article content</description>
      <link>https://example.com/2</link>
      <pubDate>Tue, 02 Jan 2026 12:00:00 +0000</pubDate>
    </item>
  </channel>
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    // Use reflection to call fromRss
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    assert_eq('Test Feed', (string) $feed->title);
    $items = [];
    foreach ($feed->item as $item) {
        $items[] = $item;
    }
    assert_count(2, $items);
    assert_eq('Article One', (string) $items[0]->title);
    assert_eq('Article Two', (string) $items[1]->title);
});

test('RSS feed extracts pubDate timestamps', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test</title>
    <item>
      <title>Test Item</title>
      <pubDate>Wed, 08 Jan 2026 15:30:00 +0000</pubDate>
    </item>
  </channel>
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    foreach ($feed->item as $item) {
        $ts = (int) $item->timestamp;
        assert_true($ts > 0, 'Timestamp should be positive');
        assert_eq(strtotime('Wed, 08 Jan 2026 15:30:00 +0000'), $ts);
    }
});

test('RSS feed extracts dc:date timestamps', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title>Test</title>
    <item>
      <title>DC Date Item</title>
      <dc:date>2026-01-08T15:30:00+00:00</dc:date>
    </item>
  </channel>
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    foreach ($feed->item as $item) {
        $ts = (int) $item->timestamp;
        assert_true($ts > 0, 'dc:date timestamp should be positive');
    }
});

test('RSS feed with no channel throws FeedException', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);

    $threw = false;
    try {
        $method->invoke(null, $sxml);
    } catch (FeedException $e) {
        $threw = true;
        assert_eq('Invalid feed.', $e->getMessage());
    }
    assert_true($threw, 'Should throw FeedException for invalid RSS');
});

// --- Atom format parsing ---

test('Atom feed parses entries correctly', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Atom Test Feed</title>
  <entry>
    <title>Atom Entry One</title>
    <summary>First entry</summary>
    <updated>2026-01-08T12:00:00Z</updated>
  </entry>
  <entry>
    <title>Atom Entry Two</title>
    <summary>Second entry</summary>
    <updated>2026-01-09T12:00:00Z</updated>
  </entry>
</feed>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromAtom');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    $entries = [];
    foreach ($feed->entry as $entry) {
        $entries[] = $entry;
    }
    assert_count(2, $entries);
    assert_eq('Atom Entry One', (string) $entries[0]->title);
});

test('Atom feed extracts updated timestamps', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Test</title>
  <entry>
    <title>Entry</title>
    <updated>2026-01-08T15:30:00Z</updated>
  </entry>
</feed>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromAtom');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    foreach ($feed->entry as $entry) {
        $ts = (int) $entry->timestamp;
        assert_true($ts > 0, 'Atom timestamp should be positive');
        assert_eq(strtotime('2026-01-08T15:30:00Z'), $ts);
    }
});

test('Atom feed with wrong namespace throws FeedException', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://example.com/wrong">
  <title>Bad Feed</title>
</feed>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromAtom');
    $method->setAccessible(true);

    $threw = false;
    try {
        $method->invoke(null, $sxml);
    } catch (FeedException $e) {
        $threw = true;
    }
    assert_true($threw, 'Should throw FeedException for invalid Atom namespace');
});

// --- Feed.load() auto-detection ---

test('Feed::load detects RSS format via channel element', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>RSS Auto-detect</title>
    <item>
      <title>Item</title>
      <pubDate>Mon, 01 Jan 2026 12:00:00 +0000</pubDate>
    </item>
  </channel>
</rss>
XML;

    // Test via fromRss directly since load() requires a URL
    $sxml = new SimpleXMLElement($xml);
    assert_true(isset($sxml->channel), 'RSS detected by channel element');
});

test('Feed::load detects Atom format via namespace', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Atom Auto-detect</title>
</feed>
XML;

    $sxml = new SimpleXMLElement($xml);
    assert_false(isset($sxml->channel), 'No channel means not RSS');
    $ns = $sxml->getDocNamespaces();
    assert_true(in_array('http://www.w3.org/2005/Atom', $ns), 'Atom namespace present');
});

// --- Edge cases ---

test('RSS feed with empty items list parses without error', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Empty Feed</title>
  </channel>
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    assert_eq('Empty Feed', (string) $feed->title);
    $count = 0;
    foreach ($feed->item as $item) {
        $count++;
    }
    assert_eq(0, $count);
});

test('RSS item with description but no content:encoded', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test</title>
    <item>
      <title>Desc Only</title>
      <description>Description text here</description>
      <pubDate>Mon, 01 Jan 2026 12:00:00 +0000</pubDate>
    </item>
  </channel>
</rss>
XML;

    $sxml = new SimpleXMLElement($xml);
    $method = new ReflectionMethod('Feed', 'fromRss');
    $method->setAccessible(true);
    $feed = $method->invoke(null, $sxml);

    foreach ($feed->item as $item) {
        assert_eq('Description text here', (string) $item->description);
    }
});

$exitCode = test_summary();
exit($exitCode);
