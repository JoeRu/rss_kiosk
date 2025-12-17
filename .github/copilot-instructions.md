# RSS News Kiosk - AI Coding Agent Instructions

## Project Overview
RSS News Kiosk is a **split-stack application** with a PHP REST API backend and Svelte SPA frontend, designed as a kiosk-style news viewer. The frontend displays RSS news articles in an auto-playing carousel with manual controls.

## Architecture

### Backend (PHP)
- **REST API** at `/api/index.php` - Four endpoints: `/news`, `/config`, `/status`, `/refresh`
- **FeedService** (`api/FeedService.php`) - Core service handling RSS fetching, caching, content cleaning, and QR code generation
- **Feed Parser** (`src/Feed.php`) - RSS/Atom parsing via `dg/rss-php` library
- **Configuration** (`api/config.php`) - Returns array with feeds, caching, and display settings

**Key patterns:**
- File-based caching in `cache/feed_cache.json` with TTL validation
- Content cleaning removes "[mehr]" suffixes and tagesschau.de promotional text
- QR codes generated using `chillerlan/php-qrcode` as base64 data URIs
- Both RSS (`<item>`) and Atom (`<entry>`) formats supported
- **Image extraction:** `extractImages()` parses HTML, extracts all `<img>` src URLs, returns separate array
- **Content shortening:** `shortenContent()` limits to 2 sentences OR 70 chars, preserves sentence boundaries

### Frontend (Svelte)
- **App.svelte** - Root component, handles initial data loading and settings modal
- **NewsCarousel.svelte** - Main carousel with auto-play (18s intervals), manual controls, pause/play
- **NewsItem.svelte** - Individual news card with title, timestamp, content, image, QR code
- **newsStore.js** - Svelte store managing news state, API calls, and auto-refresh intervals

**State management:**
- `newsStore` - News items array
- `configStore` - API configuration (refresh interval, max items, feed count)
- Auto-refresh runs based on `config.refreshInterval` from API

## Development Workflow

### Setup (first time)
```powershell
# 1. CRITICAL: Enable required PHP extensions first
# Edit php.ini (find location with: php --ini)
# Uncomment these lines:
#   extension=openssl
#   extension=mbstring
# Verify with: php -m | Select-String "openssl|mbstring"

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
cd frontend
npm install

# 4. Copy environment configuration (optional - defaults work)
cp .env.example .env.local
cd ..
```

**⚠️ Common Setup Issues:**
- **"openssl extension is required"** → Enable in php.ini: `extension=openssl`
- **"ext-mbstring * is missing"** → Enable in php.ini: `extension=mbstring`
- **Composer errors about curl** → Non-critical warning, install continues
- **No vendor/ directory** → Backend will fail with 500 errors until `composer install` runs

**Composer Workflow:**
- First run creates `composer.lock` (commit this to git)
- `composer install` uses lock file for reproducible builds
- `composer update` updates dependencies and regenerates lock file
- Dependencies: `dg/rss-php` (RSS parser), `chillerlan/php-qrcode` (QR codes)
- Requires PHP 7.4+, ext-simplexml, ext-openssl, ext-mbstring
- No `composer.lock` in repo = fresh install will resolve latest versions matching constraints in `composer.json`

### Development servers
**Three options:**

1. **VS Code Tasks (Recommended):**
   - Press `Ctrl+Shift+P` → "Run Task" → "Start Dev Environment"
   - This starts both PHP server (localhost:8000) and Vite (localhost:5173)
   - Access at `http://localhost:5173`
   - Stop servers: `Ctrl+C` in each terminal panel

2. **Manual - PHP built-in server:**
   ```powershell
   # Terminal 1 - PHP Backend (MUST use 127.0.0.1, not localhost!)
   php -S 127.0.0.1:8000 -t .
   
   # Terminal 2 - Vite Frontend
   cd frontend
   npm run dev
   ```
   - Access at `http://localhost:5173`
   - Vite proxies `/api` to `http://127.0.0.1:8000`
   - **⚠️ IPv4/IPv6 Issue:** Use `127.0.0.1` not `localhost` to avoid binding to IPv6 (::1) which causes ECONNREFUSED errors

3. **Apache (custom setup):**
   - If using Apache on custom port (e.g., 8087)
   - Update `frontend/.env.local`: `VITE_API_TARGET=http://localhost:8087`
   - Only start Vite: `cd frontend && npm run dev`
   - Access at `http://localhost:5173`

### Production build
```powershell
cd frontend
npm run build  # Outputs to ../dist/
```
- `.htaccess` handles routing: root redirects to `dist/`, API requests go to `api/`, SPA fallback to `dist/index.html`
- Base path is `/rss_kiosk/dist/` (configured in `vite.config.js`)

## Project-Specific Conventions

### File Structure
- `api/` - PHP backend (not inside `frontend/`)
- `frontend/src/` - Svelte source files
- `src/` - Legacy PHP RSS parser (still used by FeedService)
- `dist/` - Built frontend (generated, not in git)
- `cache/` - Runtime cache directory (must be writable)

### Configuration editing
- **RSS feeds:** Edit `api/config.php` `feeds` array
- **Display settings:** `api/config.php` under `display` key
- **Carousel speed:** Change `autoplaySpeed` in `NewsCarousel.svelte` (milliseconds)
- **API base URL:** Hardcoded as `../api` in `newsStore.js` (relative path)
- **Environment settings:** Edit `frontend/.env.local` for base path and API target
  - `VITE_BASE_PATH` - Application base path (default: `/rss_kiosk`)
  - `VITE_API_TARGET` - Backend URL for proxy (default: `http://localhost:8000`)

### Code patterns
- **PHP:** All endpoints return JSON with `{success: bool, data: any, ...}` structure
- **Svelte:** Use stores for state, `$newsStore` auto-subscription syntax
- **API calls:** Always through `newsStore.js` functions, never direct fetch in components
- **Content cleaning:** Done server-side in `FeedService::cleanContent()`
- **CORS:** Handled via PHP headers and Vite proxy (no CORS issues in dev)

### Debugging

**Backend Issues:**
- **500 Internal Server Error:**
  - Check if `vendor/` exists: `Test-Path vendor`
  - Run `composer install` if missing
  - Test PHP directly: `php -r "require 'vendor/autoload.php'; echo 'OK';"`
  - Check PHP extensions: `php -m | Select-String "openssl|mbstring|simplexml"`
- **No news displayed:**
  - Test API: `Invoke-RestMethod http://127.0.0.1:8000/api/index.php?path=/news`
  - Check RSS feed URLs are accessible
  - Uncomment `error_log()` statements in `FeedService.php`
  - Verify `cache/` directory is writable
- **Cache not updating:**
  - Delete `cache/feed_cache.json`
  - Force refresh: `curl "http://127.0.0.1:8000/api/index.php?path=/refresh"`
  - Check TTL setting in `api/config.php` (default 300s)

**Frontend Issues:**
- **Blank page:**
  - Check browser console for errors (F12)
  - Verify Vite is running on port 5173
  - Test API endpoint: open `http://localhost:5173/api/index.php?path=/config`
- **Vite proxy errors (ECONNREFUSED):**
  1. Verify PHP server is running: `Get-NetTCPConnection -LocalPort 8000`
  2. Check it's on 127.0.0.1 (IPv4), not ::1 (IPv6)
  3. Test API directly: `Invoke-RestMethod http://127.0.0.1:8000/api/index.php?path=/config`
  4. Check `vite.config.js` API_TARGET default matches actual server
  5. Verify `.env.local` VITE_API_TARGET if it exists
  6. Restart Vite: press `r + Enter` in Vite terminal or `Ctrl+C` and restart
- **Images not showing:**
  - Check network tab (F12) for image load errors
  - Verify `images` array in API response
  - Check CORS headers if images from external domains
- **Text not shortened:**
  - Verify `shortenContent()` is called in `FeedService.php`
  - Check if content has sentence boundaries (. ! ?)
  - Test with: `php -r "include 'api/FeedService.php'; /*test code*/"`

**Environment Issues:**
- **Port already in use:**
  - PHP: `Get-Process -Id (Get-NetTCPConnection -LocalPort 8000).OwningProcess | Stop-Process`
  - Vite: `Get-Process -Id (Get-NetTCPConnection -LocalPort 5173).OwningProcess | Stop-Process`
- **PHP server keeps stopping:**
  - Run from project root: `cd C:\path\to\rss_kiosk; php -S 127.0.0.1:8000 -t .`
  - Check for fatal errors in terminal output
  - Verify `index.php` exists in project root
- **npm install fails:**
  - Clear cache: `npm cache clean --force`
  - Delete `node_modules` and `package-lock.json`, retry
  - Check Node.js version: `node --version` (need 16+)

## Key Integration Points

### Frontend ↔ Backend API
- Frontend calls `/api/index.php` with `?path=` parameter
- Force refresh via `?refresh=true` query parameter
- Auto-refresh interval from API config, not hardcoded in frontend

### Vite Development Proxy
- Configured in `vite.config.js` using environment vari127.0.0.1:8000`
- Proxy does NOT rewrite path in dev mode - passes `/api` through directly
- **Critical:** Vite default in code MUST match actual PHP server address (127.0.0.1:8000)
- Override via environment variables if using Apache or different ports
- **Network binding:** Vite serves on `0.0.0.0:5173` (all interfaces), PHP on `127.0.0.1:8000` (IPv4 only)00`
- Rewrites `/api` to `${VITE_BASE_PATH}/api` for backend routing
- Override via environment variables if using different ports/paths

### Production Routing
- `.htaccess` rules:
  1. Root (`/rss_kiosk/`) → redirects to `dist/`
  2. `/api/*` → pass through to PHP
  3. Everything else → try `dist/` static files, fallback to `dist/index.html` (SPA routing)

## Common Tasks

### Add new RSS feed
Edit `api/config.php`, add URL to `feeds` array. Cache refreshes automatically after TTL expires.

### Change refresh interval
Edit `api/config.php` `refresh_interval` (seconds). Frontend picks this up from `/config` endpoint on load.

### Modify news item display
Edit `frontend/src/components/NewsItem.svelte`. Images float left via inline styles in carousel component.

### Adjust cache behavior
Edit `api/config.php` `cache` array. Disable by setting `enabled` to `false`.

### Filter content
Add keywords to `api/config.php` `display.exclude_keywords` array. Items with matching titles are filtered out.

## Environment Notes
- **Windows development environment** (PowerShell commands)
- **Default dev setup:** PHP built-in server on `localhost:8000`, Vite on `localhost:5173`
- **VS Code tasks available:** Use "Start Dev Environment" task for one-click setup
- **Environment variables:** Configure via `frontend/.env.local` (copy from `.env.example`)
- **Deployment path:** `/rss_kiosk/` (configurable via `VITE_BASE_PATH`)
- All documentation is in **German** (README.md, comments)
- Production uses `dist/` folder, not `frontend/` directly

## VS Code Integration
- **Launch configs:** Debug in Chrome/Edge with F5
- **Tasks available:**
  - "Start Dev Environment" - Starts both PHP and Vite servers
  - "Build Frontend" - Production build (Ctrl+Shift+B)
  - "Install PHP Dependencies" - Runs composer install
  - "Install Frontend Dependencies" - Runs npm install in frontend/
- **Settings:** PHP and Svelte language support configured
