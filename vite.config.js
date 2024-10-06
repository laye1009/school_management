import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
  plugins: [
    symfonyPlugin()
  ],
  build: {
    outDir: 'public/build',
    manifest: true,
    rollupOptions: {
      input: './assets/app.js',
    },
  },
  server: {
    watch: {
      usePolling: true,
    },
  },
});
