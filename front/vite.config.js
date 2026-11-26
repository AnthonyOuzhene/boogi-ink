import { fileURLToPath, URL } from 'node:url'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    // Raccourci d'import : "@/" = dossier src/, depuis n'importe quel fichier.
    // import GalleryGrid from '@/components/gallery/GalleryGrid' (au lieu de '../../components/…')
    // Même réglage dans jsconfig.json, pour que l'éditeur (PhpStorm) suive aussi ces imports.
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    // Port fixe : 5173 (port par défaut de Vite) est déjà pris par un autre projet.
    // strictPort : si 5180 est occupé, Vite s'arrête au lieu de prendre un autre port en silence
    // (le lien « Voir le site » du backoffice, FRONT_URL dans back/.env, pointe vers ce port).
    port: 5180,
    strictPort: true,
  },
})
