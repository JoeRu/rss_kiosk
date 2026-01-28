# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

RSS News Kiosk is a split-stack news display application consisting of:
- **Backend**: PHP REST API with RSS feed aggregation, caching, and QR code generation
- **Frontend**: Svelte SPA with auto-carousel and manual controls

The system is designed for kiosk-style deployment, displaying news articles from multiple RSS feeds in an automatic slideshow format.

## Development Commands

### Initial Setup

```powershell
# 1. CRITICAL: Enable PHP extensions first (edit php.ini)
#    Uncomment: extension=openssl, extension=mbstring, extension=simplexml
#    Verify with: php -m | Select-String "openssl|mbstring|simplexml"

# 2. Install dependencies
composer install
cd frontend && npm install && cd ..
```

### Development Servers

**Recommended: VS Code Tasks**
- Press `Ctrl+Shift+P` → "Tasks: Run Task" → "Start Dev Environment"
- This starts both PHP server (127.0.0.1:8000) and Vite (localhost:5173)

**Manual Start**
```powershell
# Terminal 1 - PHP Backend (MUST use 127.0.0.1, not localhost)
php -S 127.0.0.1:8000 -t .

# Terminal 2 - Vite Frontend
cd frontend
npm run dev
```

Access at: `http://localhost:5173`

### Build & Deploy

```powershell
# Production build
cd frontend
npm run build    # Outputs to ../dist/

# Available VS Code tasks
# - "Build Frontend" (Ctrl+Shift+B)
# - "Install PHP Dependencies"
# - "Install Frontend Dependencies"
```

## Architecture

### Backend (PHP)

**REST API** (`api/index.php`)
- `GET /api/index.php?path=/news` - All news items (optional `?refresh=true`)
- `GET /api/index.php?path=/config` - Public configuration
- `GET /api/index.php?path=/status` - Cache status
- `GET /api/index.php?path=/refresh` - Force refresh

**FeedService** (`api/FeedService.php`)
- Core service handling RSS/Atom parsing, caching, content processing
- **Image extraction**: `extractImages()` parses HTML `<img>` tags, returns array of URLs
- **Content shortening**: `shortenContent()` limits to 2 sentences OR 70 chars
- **Content cleaning**: Removes "[mehr]" suffixes and promotional text
- **QR generation**: Uses `chillerlan/php-qrcode` to create base64 data URIs
- File-based caching in `cache/feed_cache.json` with TTL validation

**Feed Parser** (`src/Feed.php`)
- RSS/Atom parsing via `dg/rss-php` library
- Supports both `<item>` (RSS) and `<entry>` (Atom) formats

**Configuration** (`api/config.php`)
- Returns array with `feeds`, `cache`, `display`, and `refresh_interval` settings
- Feeds are defined here as array of URLs
- Example config available in `api/config.php.example`

### Frontend (Svelte)

**Components**
- `App.svelte` - Root component, data loading, settings modal
- `NewsCarousel.svelte` - Carousel with auto-play (18s intervals), pause/play, manual nav
- `NewsItem.svelte` - Individual news card with title, timestamp, content, image, QR code
- `Settings.svelte` - Configuration dialog

**State Management** (`stores/newsStore.js`)
- `newsStore` - News items array
- `configStore` - API configuration
- Auto-refresh based on `config.refreshInterval` from backend

**Data Flow**
```
RSS Feeds → FeedService → Cache → REST API → Svelte Store → UI Components
               ↑                                    ↓
               └──────── Auto-Refresh ──────────────┘
```

### Critical Integration Points

**Vite Development Proxy** (`vite.config.js`)
- Proxies `/api` requests to `http://127.0.0.1:8000` (default)
- Override via `VITE_API_TARGET` environment variable
- **IPv4/IPv6 Issue**: MUST use `127.0.0.1` (IPv4), NOT `localhost` (can bind to IPv6 ::1)

**Environment Configuration** (`frontend/.env.local`)
- `VITE_BASE_PATH` - Application base path (default: `/rss_kiosk`)
- `VITE_API_TARGET` - Backend URL for proxy (default: `http://127.0.0.1:8000`)
- Copy from `frontend/.env.example` if customization needed

**Production Routing** (`.htaccess`)
1. Root (`/rss_kiosk/`) → redirects to `dist/`
2. `/api/*` → pass through to PHP
3. Everything else → `dist/` static files, fallback to `dist/index.html` (SPA)

## Key Technical Details

### PHP Dependencies
- `dg/rss-php` - RSS/Atom feed parser
- `chillerlan/php-qrcode` - QR code generation
- Requires PHP 7.4+, ext-simplexml, ext-openssl, ext-mbstring
- Installed via `composer install`

### Content Processing Pipeline
1. Fetch RSS/Atom feeds (supports both formats)
2. Extract images from HTML content via regex
3. Strip HTML tags from content
4. Clean content (remove "[mehr]", promotional text)
5. Shorten content (2 sentences OR 70 chars, preserve sentence boundaries)
6. Generate QR codes for article links
7. Filter by exclude_keywords
8. Shuffle if configured
9. Limit to max_items
10. Cache with TTL validation

### API Response Format
All endpoints return JSON with this structure:
```json
{
  "success": true/false,
  "data": {...},
  "count": 123,
  "timestamp": 1234567890,
  "error": "message" // only on failure
}
```

### Cache Behavior
- File-based: `cache/feed_cache.json`
- TTL from `config.php` (default 300s)
- Validated by file modification time
- Force refresh via `?refresh=true` parameter

## Configuration

### Adding RSS Feeds
Edit `api/config.php`, add URLs to `feeds` array:
```php
'feeds' => [
    'https://www.tagesschau.de/xml/rss2',
    'https://rss.sueddeutsche.de/rss/Alles',
    // Add more feeds here
]
```

### Adjusting Display Settings
In `api/config.php`:
- `refresh_interval` - Auto-refresh seconds (default: 300)
- `display.max_items` - Max news items (default: 50)
- `display.shuffle` - Randomize order (default: true)
- `display.exclude_keywords` - Filter out titles containing these terms

### Changing Carousel Speed
Edit `NewsCarousel.svelte`, modify `autoplaySpeed` constant (milliseconds, default: 18000)

## Common Issues

### Backend Problems

**500 Internal Server Error / No vendor/ directory**
```powershell
# Verify vendor exists
Test-Path vendor

# Install dependencies
composer install

# Verify PHP extensions
php -m | Select-String "openssl|mbstring|simplexml"
```

**"openssl/mbstring extension is required"**
- Edit php.ini (find with `php --ini`)
- Uncomment: `extension=openssl` and `extension=mbstring`
- Restart PHP server

**No news displayed**
```powershell
# Test API directly
Invoke-RestMethod http://127.0.0.1:8000/api/index.php?path=/news

# Check feed URLs are accessible
# Verify cache/ directory is writable

# Force refresh
curl "http://127.0.0.1:8000/api/index.php?path=/refresh"
```

### Frontend Problems

**Vite proxy errors (ECONNREFUSED)**
1. Verify PHP server is running: `Get-NetTCPConnection -LocalPort 8000`
2. Check it's on 127.0.0.1 (IPv4), not ::1 (IPv6)
3. Test API directly: `Invoke-RestMethod http://127.0.0.1:8000/api/index.php?path=/config`
4. Check `vite.config.js` API_TARGET matches actual server
5. Restart Vite: press `r + Enter` in terminal or `Ctrl+C` and restart

**Blank page**
- Check browser console (F12) for errors
- Verify Vite is running on port 5173
- Test API endpoint: `http://localhost:5173/api/index.php?path=/config`

### Port Conflicts
```powershell
# Find process using port
netstat -ano | findstr :8000

# Kill process (use PID from above)
taskkill /PID <PID> /F

# Or use different port
npm run dev -- --port 5174
```

## Development Notes

- All documentation is in German (README.md, comments)
- Windows development environment (PowerShell commands)
- VS Code tasks available for common operations
- Default timezone: Europe/Berlin (set in FeedService)
- Production build outputs to `dist/` (not in git)
- Cache directory must be writable by PHP process
