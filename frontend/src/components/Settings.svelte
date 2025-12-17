<script>
  import { createEventDispatcher, onMount } from 'svelte';
  import { configStore, loadConfig, refreshNews, startAutoRefresh } from '../stores/newsStore.js';

  const dispatch = createEventDispatcher();

  let refreshInterval = 300;
  let isRefreshing = false;
  let lastRefresh = new Date().toLocaleTimeString();

  onMount(async () => {
    const config = await loadConfig();
    refreshInterval = config.refreshInterval;
  });

  async function handleRefresh() {
    isRefreshing = true;
    try {
      await refreshNews();
      lastRefresh = new Date().toLocaleTimeString();
    } catch (error) {
      alert('Failed to refresh news: ' + error.message);
    } finally {
      isRefreshing = false;
    }
  }

  function handleIntervalChange() {
    startAutoRefresh(refreshInterval);
    alert(`Auto-refresh interval updated to ${refreshInterval} seconds`);
  }

  function close() {
    dispatch('close');
  }
</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<div class="settings-overlay" on:click={close}>
  <!-- svelte-ignore a11y-click-events-have-key-events -->
  <!-- svelte-ignore a11y-no-static-element-interactions -->
  <div class="settings-panel" on:click|stopPropagation>
    <div class="settings-header">
      <h2>Settings</h2>
      <button class="close-btn" on:click={close}>✕</button>
    </div>

    <div class="settings-content">
      <div class="setting-group">
        <label for="refresh-interval">
          Auto-refresh interval (seconds):
        </label>
        <input
          id="refresh-interval"
          type="number"
          bind:value={refreshInterval}
          min="30"
          max="3600"
          step="30"
        />
        <button class="apply-btn" on:click={handleIntervalChange}>
          Apply
        </button>
      </div>

      <div class="setting-group">
        <p class="setting-label">Manual refresh:</p>
        <button 
          class="refresh-btn" 
          on:click={handleRefresh}
          disabled={isRefreshing}
        >
          {isRefreshing ? 'Refreshing...' : '🔄 Refresh Now'}
        </button>
        <small>Last refresh: {lastRefresh}</small>
      </div>

      <div class="setting-group">
        <p class="setting-label">Feed info:</p>
        <div class="info">
          <p>Feed sources: {$configStore.feedCount}</p>
          <p>Max items: {$configStore.maxItems}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .settings-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .settings-panel {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      transform: translateY(50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 2px solid #ecf0f1;
  }

  .settings-header h2 {
    margin: 0;
    color: #2c3e50;
  }

  .close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #7f8c8d;
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
  }

  .close-btn:hover {
    background: #ecf0f1;
    color: #2c3e50;
  }

  .settings-content {
    padding: 20px;
  }

  .setting-group {
    margin-bottom: 30px;
  }

  .setting-group:last-child {
    margin-bottom: 0;
  }

  label,
  .setting-label {
    display: block;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
  }

  input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 2px solid #ecf0f1;
    border-radius: 6px;
    font-size: 1rem;
    margin-bottom: 10px;
    transition: border-color 0.3s ease;
  }

  input[type="number"]:focus {
    outline: none;
    border-color: #667eea;
  }

  .apply-btn, .refresh-btn {
    width: 100%;
    padding: 12px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .apply-btn:hover, .refresh-btn:hover:not(:disabled) {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .refresh-btn:disabled {
    background: #95a5a6;
    cursor: not-allowed;
  }

  small {
    display: block;
    margin-top: 10px;
    color: #7f8c8d;
    font-style: italic;
  }

  .info {
    background: #ecf0f1;
    padding: 15px;
    border-radius: 6px;
  }

  .info p {
    margin: 5px 0;
    color: #2c3e50;
  }
</style>
