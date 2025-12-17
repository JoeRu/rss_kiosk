<script>
  import { onMount, onDestroy } from 'svelte';
  import NewsItem from './NewsItem.svelte';
  import { startAutoRefresh, stopAutoRefresh } from '../stores/newsStore.js';

  export let news = [];
  export let config = {};

  let currentIndex = 0;
  let autoplaySpeed = 18000; // 18 seconds per slide
  let autoplayTimer = null;
  let isPaused = false;

  onMount(() => {
    startAutoplay();
    
    // Start auto-refresh based on config
    if (config.refreshInterval) {
      startAutoRefresh(config.refreshInterval);
    }
  });

  onDestroy(() => {
    stopAutoplay();
    stopAutoRefresh();
  });

  function startAutoplay() {
    autoplayTimer = setInterval(() => {
      if (!isPaused) {
        nextSlide();
      }
    }, autoplaySpeed);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % news.length;
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + news.length) % news.length;
  }

  function goToSlide(index) {
    currentIndex = index;
  }

  function togglePause() {
    isPaused = !isPaused;
  }

  $: if (news.length > 0 && currentIndex >= news.length) {
    currentIndex = 0;
  }
</script>

<div class="carousel-container">
  {#if news.length === 0}
    <div class="no-news">
      <p>No news items available</p>
    </div>
  {:else}
    <div class="carousel">
      <div class="slides" style="transform: translateX(-{currentIndex * 100}%)">
        {#each news as item, index}
          <div class="slide" class:active={index === currentIndex}>
            <NewsItem {item} />
          </div>
        {/each}
      </div>

      <div class="controls">
        <button class="control-btn prev" on:click={prevSlide} title="Previous">
          ‹
        </button>
        <button class="control-btn pause" on:click={togglePause} title={isPaused ? 'Play' : 'Pause'}>
          {isPaused ? '▶' : '❚❚'}
        </button>
        <button class="control-btn next" on:click={nextSlide} title="Next">
          ›
        </button>
      </div>

      <div class="indicators">
        {#each news as _, index}
          <button
            class="indicator"
            class:active={index === currentIndex}
            on:click={() => goToSlide(index)}
            title="Go to slide {index + 1}"
          />
        {/each}
      </div>

      <div class="counter">
        {currentIndex + 1} / {news.length}
      </div>
    </div>
  {/if}
</div>

<style>
  /* Reset paragraph margins globally */
  p { 
    margin-top: 0; 
    margin-bottom: 0; 
    padding-top: 0; 
    padding-bottom: 0; 
  }

  .carousel-container {
    position: relative;
    width: 100vw;
    max-width: 100vw;
    margin: 0;
    padding: 0;
    overflow: hidden;
  }

  .carousel {
    position: relative;
    overflow: hidden;
    background: #000000;
    border-radius: 0;
    box-shadow: none;
    min-height: 100vh;
    width: 100%;
  }

  .slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
    width: 100%;
  }

  .slide {
    min-width: 100%;
    max-width: 100%;
    flex-shrink: 0;
    box-sizing: border-box;
  }

  .controls {
    display: none;
  }

  .control-btn {
    pointer-events: all;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    font-size: 2rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .control-btn:hover {
    background: rgba(0, 0, 0, 0.8);
    transform: scale(1.1);
  }

  .control-btn.pause {
    font-size: 1.2rem;
  }

  .indicators {
    display: none;
  }

  .indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(0, 0, 0, 0.3);
    cursor: pointer;
    padding: 0;
    transition: all 0.3s ease;
  }

  .indicator:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.2);
  }

  .indicator.active {
    background: white;
    border-color: #667eea;
    transform: scale(1.3);
  }

  .counter {
    display: none;
  }

  .no-news {
    text-align: center;
    padding: 60px 20px;
    background: #000000;
    border-radius: 0;
    box-shadow: none;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .no-news p {
    font-size: 1.2rem;
    color: #ffffff;
  }

  @media (max-width: 768px) {
    .control-btn {
      width: 40px;
      height: 40px;
      font-size: 1.5rem;
    }

    .control-btn.pause {
      font-size: 1rem;
    }

    .controls {
      padding: 0 10px;
    }

    .counter {
      font-size: 0.8rem;
      padding: 6px 12px;
    }
  }
</style>
