# Content and Image Processing Improvements

## Changes Made

### Backend (FeedService.php)

#### 1. Image Extraction (`extractImages()`)
- **New method** that extracts all image URLs from RSS content
- Supports various image formats in HTML `<img>` tags
- Validates URLs (must start with `http://` or `https://` or `//`)
- Removes duplicates and returns clean array of image URLs
- Images are now returned in separate `images` array in API response

#### 2. Content Shortening (`shortenContent()`)
- **New method** that intelligently shortens long text
- **Rules:**
  - If content has **more than 2 sentences** → shorten to 1-2 sentences
  - If content is **longer than 70 characters** → shorten to 1-2 sentences
  - If first sentence is very short (<40 chars), includes second sentence
- Preserves sentence endings (. ! ?)
- Cleans up extra whitespace

#### 3. Processing Flow
```
RSS Content → Extract Images → Clean HTML → Shorten Text → Return Separate Data
```

### Frontend (NewsItem.svelte)

#### 1. Separate Image Display
- Images now displayed in dedicated `.content-images` container
- Images float to the left of text content
- Each image gets proper sizing constraints (max 30vw width, 45vh height)
- Multiple images stack vertically with spacing

#### 2. Text Display
- Text is now plain text (not HTML) to avoid embedded images
- Any remaining `<img>` tags in content are hidden via CSS
- Cleaner, more predictable layout

## API Response Format

### Before
```json
{
  "title": "News Title",
  "content": "<img src='...'> Long text with embedded images..."
}
```

### After
```json
{
  "title": "News Title",
  "content": "Shortened text without images.",
  "images": ["https://example.com/image1.jpg", "https://example.com/image2.jpg"]
}
```

## Visual Layout

```
┌─────────────────────────────────────┐
│ [QR Code]        News Title         │
│                                     │
│ [Image 1]        Shortened text     │
│ [Image 2]        content appears    │
│                  here to the right  │
│                  of images...       │
│                                     │
│                  Timestamp          │
└─────────────────────────────────────┘
```

## Testing

1. **Clear cache** to force fresh data:
   ```powershell
   Remove-Item cache\feed_cache.json -Force
   ```

2. **Start development servers** (if not running):
   ```powershell
   # PHP Backend
   php -S localhost:8000
   
   # Vite Frontend (in new terminal)
   cd frontend
   npm run dev
   ```

3. **View at** `http://localhost:5173`

## Benefits

✅ **Better visibility** - Images always visible, not hidden in long text  
✅ **Cleaner layout** - Predictable image positioning  
✅ **Faster reading** - Shortened text focuses on key information  
✅ **Consistent display** - All news items follow same visual pattern  
✅ **Multiple images** - Can show all images from content, not just first one
