<script>
  export let item;
</script>

<div class="news-item">
  <div class="news-content">

    {#if item.qrcode}
      <div class="qr-code">
        <img src={item.qrcode} alt="QR Code" width="250" />
      </div>
    {/if}
  
    <h3>{item.title}</h3>
   
    <div class="news-text">
      {@html item.content}
    </div>
    
  </div>
  
  <div class="news-footer">
    <span class="timestamp">{item.timestamp}</span>
  </div>
</div>

<style>
  .news-item {
    padding: 40px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #000000;
    overflow: hidden;
    width: 100%;
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
    margin: 0 0 20px 20px;
    z-index: 10;
  }

  .qr-code img {
    border-radius: 0;
    box-shadow: none;
    width: 200px;
    height: auto;
    display: block;
  }

  h3 {
    margin: 0 0 30px 0;
    padding: 0;
    font-size: 2.2rem;
    color: #ffffff;
    line-height: 1.3;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  .news-text {
    font-size: 1.6rem;
    line-height: 1.9;
    color: #ffffff;
    margin-bottom: 20px;
    clear: none;
    overflow-wrap: break-word;
    word-wrap: break-word;
  }

  .news-text :global(img) {
    max-width: 480px;
    max-height: 360px;
    width: auto;
    height: auto;
    object-fit: contain;
    float: left;
    margin: 10px 30px 10px 0;
    border-radius: 0;
    clear: left;
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
      padding: 20px;
      min-height: 400px;
    }

    h3 {
      font-size: 1.5rem;
    }

    .news-text {
      font-size: 1rem;
    }

    .qr-code {
      float: none;
      text-align: center;
      margin: 0 0 20px 0;
    }

    .qr-code img {
      width: 150px;
    }

    .news-text :global(img) {
      max-width: 100%;
      float: none;
      display: block;
      margin: 10px auto;
    }
  }
</style>
