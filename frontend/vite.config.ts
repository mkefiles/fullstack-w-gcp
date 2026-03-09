import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'

// NOTE: Reference: https://vite.dev/config/
// NOTE Updating `host` allows the server to accept
// ... connections from outside the container.
export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 5173,
  },
  base: "",
  plugins: [react()],
})