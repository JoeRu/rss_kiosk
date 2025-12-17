<script>
  /**
   * NewsItem Component
   * Displays a single news article with title, metadata, images, and content
   * @prop {Object} item - News item object containing title, timestamp, link, images, content, and qrcode
   */
  export let item;
  
  /**
   * Extracts the base domain from a URL
   * @param {string} url - Full URL of the news source
   * @returns {string} - Hostname (e.g., "tagesschau.de")
   */
  function getBaseDomain(url) {
    try {
      const urlObj = new URL(url);
      return urlObj.hostname;
    } catch {
      return '';
    }
  }
</script>

<!-- Main news item container -->
<div class="news-item">
  <div class="news-content">
    
    <!-- QR Code for article link (floats right) -->
    {#if item.qrcode}
      <div class="qr-code">
        <img src={item.qrcode} alt="QR Code" width="250" />
      </div>
    {/if}
  
    <!-- Article title -->
    <h3>{item.title}</h3>
    
    <!-- Source domain and timestamp -->
    <div class="meta-info">
      {#if item.link}
        {getBaseDomain(item.link)} -
      {/if}
      {item.timestamp}
    </div>

    <!-- Article images (float left if present) -->
    {#if item.images && item.images.length > 0}
      <div class="content-images">
        {#each item.images as imageSrc, index}
          <img src={imageSrc} alt="News image {index + 1}" class="content-image" />
        {/each}
      </div>
    {/if}
   
    <!-- Article text content -->
    <div class="news-text">
      {item.content}
    </div>
    
  </div>
</div>

<style>
  /* Main container - Full viewport height with black background */
  .news-item {
    padding: 4vh 5vw;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #000000;
    overflow: hidden;
    width: 100%;
    height: 100vh;
    box-sizing: border-box;
  }

  /* Content wrapper with overflow control */
  .news-content {
    flex: 1;
    display: block;
    max-height: 100%;
    overflow: hidden;
    position: relative;
  }

  /* QR Code - Floats to the right */
  .qr-code {
    float: right;
    margin: 0 0 2vh 2vw;
    z-index: 10;
  }

  .qr-code img {
    width: 10vw;
    height: 10vw;
    display: block;
  }

  /* Article title - Large white text */
  h3 {
    margin: 0 0 1vh 0;
    padding: 0;
    font-size: 8vh;
    color: #ffffff;
    line-height: 1.2;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  /* Metadata line - Source domain and timestamp */
  .meta-info {
    margin: 0 0 2vh 0;
    font-size: 3vh;
    color: #888888;
    line-height: 1;
  }

  /* Image container - Floats to the left */
  .content-images {
    float: left;
    margin: 0 3vw 2vh 0;
    max-width: 30vw;
  }

  /* Individual content images */
  .content-image {
    max-width: 30vw;
    max-height: 45vh;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-bottom: 1vh;
  }

  /* Article text content */
  .news-text {
    font-size: 6vh;
    line-height: 10vh;
    color: #ffffff;
    margin-bottom: 2vh;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  /* Hide any images embedded in text content (already shown separately) */
  .news-text :global(img) {
    display: none;
  }

  /* Mobile responsive layout */
  @media (max-width: 768px) {
    .news-item {
      padding: 2vh 3vw;
    }

    h3 {
      font-size: 6vh;
    }

    .news-text {
      font-size: 4vh;
      line-height: 8vh;
    }

    /* QR code centered on mobile */
    .qr-code {
      float: none;
      text-align: center;
      margin: 0 0 2vh 0;
    }

    .qr-code img {
      width: 20vw;
      height: 20vw;
    }

    /* Images centered on mobile */
    .content-images {
      float: none;
      margin: 0 auto 2vh;
      max-width: 50vw;
    }

    .content-image {
      max-width: 50vw;
    }
  }
</style>
