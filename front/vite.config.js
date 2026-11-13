import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    // Port fixe : 5173 (port par défaut de Vite) est déjà pris par un autre projet.
    // strictPort : si 5180 est occupé, Vite s'arrête au lieu de prendre un autre port en silence
    // (le lien « Voir le site » du backoffice, FRONT_URL dans back/.env, pointe vers ce port).
    port: 5180,
    strictPort: true,
  },
})
