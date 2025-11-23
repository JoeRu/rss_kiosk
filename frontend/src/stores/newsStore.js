import { writable } from 'svelte/store';

// API base URL - adjust this based on your setup
const API_BASE = '../api';

export const newsStore = writable([]);
export const configStore = writable({
  refreshInterval: 300,
  maxItems: 50,
  feedCount: 0
});

let refreshTimer = null;

/**
 * Fetch news from API
 */
export async function loadNews(forceRefresh = false) {
  try {
    const url = forceRefresh ? `${API_BASE}/index.php?path=/news&refresh=true` : `${API_BASE}/index.php?path=/news`;
    const response = await fetch(url);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (data.success) {
      newsStore.set(data.data);
      return data.data;
    } else {
      throw new Error(data.error || 'Failed to load news');
    }
  } catch (error) {
    console.error('Error loading news:', error);
    throw error;
  }
}

/**
 * Load configuration from API
 */
export async function loadConfig() {
  try {
    const response = await fetch(`${API_BASE}/index.php?path=/config`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (data.success) {
      configStore.set(data.data);
      return data.data;
    } else {
      throw new Error(data.error || 'Failed to load config');
    }
  } catch (error) {
    console.error('Error loading config:', error);
    throw error;
  }
}

/**
 * Start auto-refresh timer
 */
export function startAutoRefresh(intervalSeconds) {
  stopAutoRefresh(); // Clear any existing timer
  
  const intervalMs = intervalSeconds * 1000;
  
  refreshTimer = setInterval(async () => {
    try {
      await loadNews();
      console.log('News auto-refreshed at', new Date().toLocaleTimeString());
    } catch (error) {
      console.error('Auto-refresh failed:', error);
    }
  }, intervalMs);
  
  console.log(`Auto-refresh started: every ${intervalSeconds} seconds`);
}

/**
 * Stop auto-refresh timer
 */
export function stopAutoRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
    console.log('Auto-refresh stopped');
  }
}

/**
 * Force refresh news
 */
export async function refreshNews() {
  return await loadNews(true);
}
