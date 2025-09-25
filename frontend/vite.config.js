// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: '/kagomori/',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: false,
    minify: 'esbuild', // Use esbuild instead of terser as fallback
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue']
        }
      }
    },
    // Ensure all files from public directory are copied
    copyPublicDir: true
  },
  server: {
    port: 3000,
    host: true
  }
})
