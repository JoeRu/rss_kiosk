# Quick Start Guide

## Development Setup (5 minutes)

### 1. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
cd frontend
npm install
cd ..
```

### 2. Configure Feeds
Edit `api/config.php` to add your RSS feeds:
```php
'feeds' => [
    'https://www.tagesschau.de/xml/rss2',
    'https://rss.sueddeutsche.de/rss/Alles',
    // Add more...
]
```

### 3. Start Development Servers

**If using Apache (port 8087):**
- Apache should already be running
- Just start Vite:
```powershell
cd frontend
npm run dev
```

**If using PHP built-in server:**
```powershell
# Terminal 1 - PHP Backend
php -S 0.0.0.0:8000

# Terminal 2 - Svelte Frontend
cd frontend
npm run dev
```
*(Note: Update vite.config.js target to port 8000)*

### 4. Open Browser
Navigate to `http://joesnuc:5173`

## Production Deployment

### Build Frontend
```bash
cd frontend
npm run build
```

### Deploy
1. Copy all files to your web server
2. Configure web server (see `.htaccess` or `nginx.conf.example`)
3. Ensure `cache/` directory is writable
4. Done!

## Customization

### Change Refresh Interval
In `api/config.php`:
```php
'refresh_interval' => 300, // seconds
```

Or use the Settings panel in the UI (⚙️ button)

### Add/Remove RSS Feeds
Edit `api/config.php`:
```php
'feeds' => [
    'https://example.com/feed.xml',
]
```

### Adjust Carousel Speed
In `frontend/src/components/NewsCarousel.svelte`:
```javascript
let autoplaySpeed = 18000; // milliseconds
```

## Troubleshooting

### CORS Issues
- Development: Vite proxy handles this automatically
- Production: Check your `.htaccess` or nginx config

### Cache Not Working
- Ensure `cache/` directory exists and is writable
- Check permissions: `chmod 755 cache/`

### API Returns Errors
- Check PHP error logs
- Verify RSS feed URLs are accessible
- Test feeds individually: `api/index.php?path=/news`

## Support

For issues, check:
1. Browser console (F12)
2. PHP error logs
3. Network tab for API calls
