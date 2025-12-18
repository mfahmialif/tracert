
import path from "node:path"
import { defineConfig } from "vite"
import vue from "@vitejs/plugin-vue"

import tailwind from "tailwindcss"
import autoprefixer from "autoprefixer"

export default defineConfig({
  css: {
    postcss: {
      plugins: [tailwind(), autoprefixer()],
    },
  },
  plugins: [vue()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  server: {
    port: 5173,
    proxy: {
      // "/api": {
      //   target: "http://localhost:8000",
      //   changeOrigin: true,
      // },
      // "/sanctum": {
      //   target: "http://localhost:8000",
      //   changeOrigin: true,
      // },
      // "/api": {
      //   target: "http://localhost/tracert/backend/public_html",
      //   changeOrigin: true,
      // },
      // "/sanctum": {
      //   target: "http://localhost/tracert/backend/public_html",
      //   changeOrigin: true,
      // },
      "/api": {
        target: "https://tracerapp.uiidalwa.web.id",
        changeOrigin: true,
      },
      "/sanctum": {
        target: "https://tracerapp.uiidalwa.web.id",
        changeOrigin: true,
      },
    },
  },
})
