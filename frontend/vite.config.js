import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

export default defineConfig({
  base: '/rss_kiosk/dist/',
  plugins: [svelte()],
  build: {
    outDir: '../dist',
    emptyOutDir: true
  },
  server: {
//    allowedHosts: ['joesnuc'],
    proxy: {
      '/api': {
//        target: 'http://myserver:port',
        changeOrigin: true,
        rewrite: (path) => '/rss-php' + path
      }
    }
  }
})
