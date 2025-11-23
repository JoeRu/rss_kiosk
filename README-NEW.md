# RSS News Viewer - Svelte + PHP

Modern RSS news viewer with Svelte frontend and PHP REST API backend.

## Architecture

### Backend (PHP)
- **REST API** (`/api/index.php`) - Provides endpoints for fetching news, config, and status
- **Feed Service** (`/api/FeedService.php`) - Handles RSS feed fetching and caching
- **Configuration** (`/api/config.php`) - Centralized configuration for feeds and settings

### Frontend (Svelte)
- **Modern SPA** with automatic carousel and manual controls
- **Auto-refresh** with configurable interval
- **QR codes** for sharing articles
- **Responsive design** for all screen sizes

## Setup

### Prerequisites
- PHP 7.4 or higher
- Node.js 16+ and npm
- Composer (for PHP dependencies)

### Installation

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install frontend dependencies:**
   ```bash
   cd frontend
   npm install
   ```

3. **Configure RSS feeds:**
   Edit `api/config.php` to customize:
   - RSS feed URLs
   - Refresh interval
   - Cache settings
   - Display options

### Development

**Using Apache (port 8087):**

1. **Make sure Apache is running** and serving from this directory

2. **Start Vite dev server:**
   ```powershell
   cd frontend
   npm run dev
   ```

3. Open browser to `http://joesnuc:5173` (or `http://localhost:5173`)

**Or using PHP built-in server:**

1. **Start PHP development server:**
   ```powershell
   php -S 0.0.0.0:8000
   ```

2. **Update `frontend/vite.config.js`** target to `http://joesnuc:8000`

3. **Start Vite and browse** as above

### Production Build

1. **Build frontend:**
   ```bash
   cd frontend
   npm run build
   ```

2. **Serve from web server:**
   The built files will be in the `dist/` folder. Configure your web server to:
   - Serve static files from `dist/`
   - Proxy `/api/*` requests to `api/index.php`

## API Endpoints

### GET `/api/index.php?path=/news`
Fetch all news items.
- Query param: `refresh=true` to force refresh

### GET `/api/index.php?path=/config`
Get public configuration settings.

### GET `/api/index.php?path=/status`
Get cache status information.

### GET `/api/index.php?path=/refresh`
Force refresh all feeds.

## Configuration

Edit `api/config.php`:

```php
return [
    'refresh_interval' => 300,  // Auto-refresh interval in seconds
    'feeds' => [
        'https://example.com/rss',
        // Add more feed URLs
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 300,  // Cache lifetime in seconds
    ],
    'display' => [
        'max_items' => 50,
        'shuffle' => true,
        'exclude_keywords' => ['keyword1', 'keyword2']
    ]
];
```

## Features

### Backend Features
- ✅ REST API with JSON responses
- ✅ Intelligent caching system
- ✅ Multiple RSS feed support
- ✅ QR code generation
- ✅ Content filtering and cleaning
- ✅ CORS support

### Frontend Features
- ✅ Modern Svelte SPA
- ✅ Automatic carousel with manual controls
- ✅ Configurable auto-refresh
- ✅ Manual refresh button
- ✅ Settings panel
- ✅ Responsive design
- ✅ Smooth animations
- ✅ QR code display
- ✅ Progress indicators

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

See `license.md`

## Credits

- Original PHP RSS parser
- [Svelte](https://svelte.dev/)
- [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode)
