<script>
  export let item;
  
  // Extract base domain from URL
  function getBaseDomain(url) {
    try {
      const urlObj = new URL(url);
      return urlObj.hostname;
    } catch {
      return '';
    }
  }
</script>

<div class="news-item">
  <div class="news-content">

    {#if item.qrcode}
      <div class="qr-code">
        <img src={item.qrcode} alt="QR Code" width="250" />
      </div>
    {/if}
  
    <h3>{item.title}</h3>
    <div class="meta-info">
      {#if item.link}
        {getBaseDomain(item.link)} -
      {/if}
      {item.timestamp}
    </div>

    {#if item.images && item.images.length > 0}
      <div class="content-images">
        {#each item.images as imageSrc, index}
          <img src={imageSrc} alt="News image {index + 1}" class="content-image" />
        {/each}
      </div>
    {/if}
   
    <div class="news-text">
      {item.content}
    </div>
    
  </div>
</div>

<style>
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

  .news-content {
    flex: 1;
    display: block;
    max-height: 100%;
    overflow: hidden;
    position: relative;
  }

  .qr-code {
    float: right;
    margin: 0 0 2vh 2vw;
    z-index: 10;
  }

  .qr-code img {
    border-radius: 0;
    box-shadow: none;
    width: 10vw;
    height: 10vw;
    display: block;
  }

  h3 {
    margin: 0 0 1vh 0;
    padding: 0;
    font-size: 8vh;
    color: #ffffff;
    line-height: 1.2;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  .meta-info {
    display: flex;
    gap: 2vw;
    margin: 0 0 2vh 0;
    font-size: 3vh;
    color: #888888;
    line-height: 1;
  }


  .content-images {
    float: left;
    margin: 0 3vw 2vh 0;
    max-width: 30vw;
  }

  .content-image {
    max-width: 30vw;
    max-height: 45vh;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin-bottom: 1vh;
    border-radius: 0;
  }

  .news-text {
    font-size: 6vh;
    line-height: 10vh;
    color: #ffffff;
    margin-bottom: 2vh;
    clear: none;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  .news-text :global(img) {
    display: none;
  }

  .news-link {
    display: none;
  }

  .news-link a {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
  }

  .news-link a:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .news-footer {
    display: none;
  }

  .news-footer-hidden {
    border-top: 2px solid #ecf0f1;
    padding-top: 20px;
    margin-top: 20px;
  }

  .timestamp {
    color: #7f8c8d;
    font-size: 0.9rem;
    font-style: italic;
  }

  @media (max-width: 768px) {
    .news-item {
      padding: 2vh 3vw;
      height: 100vh;
    }

    h3 {
      font-size: 6vh;
    }

    .news-text {
      font-size: 4vh;
      line-height: 8vh;
    }

    .qr-code {
      float: none;
      text-align: center;
      margin: 0 0 2vh 0;
    }

    .qr-code img {
      width: 20vw;
      height: 20vw;
    }

    .news-text :global(img) {
      max-width: 50vw;
      float: none;
      display: block;
      margin: 1vh auto;
    }
  }
</style>
