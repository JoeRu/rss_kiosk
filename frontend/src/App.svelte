<script>
  import NewsCarousel from './components/NewsCarousel.svelte';
  import Settings from './components/Settings.svelte';
  import { newsStore, configStore, loadNews, loadConfig } from './stores/newsStore.js';
  import { onMount } from 'svelte';

  let showSettings = false;
  let isLoading = true;
  let error = null;

  onMount(async () => {
    try {
      await loadConfig();
      await loadNews();
      isLoading = false;
    } catch (err) {
      error = err.message;
      isLoading = false;
    }
  });

  function toggleSettings() {
    showSettings = !showSettings;
  }
</script>

<main>
  <div class="header">
    <h1>Nachrichten aus Usingen</h1>
    <button class="settings-btn" on:click={toggleSettings} title="Settings">
      ⚙️
    </button>
  </div>

  {#if isLoading}
    <div class="loading">
      <div class="spinner"></div>
      <p>Loading news...</p>
    </div>
  {:else if error}
    <div class="error">
      <p>Error loading news: {error}</p>
      <button on:click={() => window.location.reload()}>Retry</button>
    </div>
  {:else}
    <NewsCarousel news={$newsStore} config={$configStore} />
  {/if}

  {#if showSettings}
    <Settings on:close={toggleSettings} />
  {/if}
</main>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: #000000;
    min-height: 100vh;
    color: #ffffff;
  }

  main {
    width: 100%;
    height: 100vh;
    margin: 0;
    padding: 0;
  }

  .header {
    display: none;
  }

  h1 {
    margin: 0;
    font-size: 2.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  }

  .settings-btn {
    display: none;
  }

  .settings-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  .loading, .error {
    text-align: center;
    padding: 60px 20px;
    background: #000000;
    color: #ffffff;
  }

  .spinner {
    border: 4px solid rgba(255, 255, 255, 0.1);
    border-left-color: #ffffff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  .error p {
    color: #ffffff;
    font-size: 1.2rem;
    margin-bottom: 20px;
  }

  .error button {
    background: #333333;
    color: white;
    border: 1px solid #ffffff;
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .error button:hover {
    background: #555555;
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 1.8rem;
    }

    .settings-btn {
      width: 40px;
      height: 40px;
      font-size: 1.2rem;
    }
  }
</style>
