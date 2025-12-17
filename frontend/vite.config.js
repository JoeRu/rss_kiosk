import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

// Environment-specific configuration
const isDev = process.env.NODE_ENV !== 'production';
const BASE_PATH = process.env.VITE_BASE_PATH || '/rss_kiosk';
const API_TARGET = process.env.VITE_API_TARGET || 'http://127.0.0.1:8000';

export default defineConfig({
  base: isDev ? '/' : `${BASE_PATH}/dist/`,
  plugins: [svelte()],
  build: {
    outDir: '../dist',
    emptyOutDir: true
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: {
      '/api': {
        target: API_TARGET,
        changeOrigin: true,
        // In dev mode, don't add base path for PHP built-in server
        // For Apache with base path, set VITE_API_TARGET to include the path
        rewrite: (path) => path
      }
    }
  }
})
