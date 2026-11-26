import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router';
import { SalonProvider } from '@/context/SalonProvider';
import App from './App';
// Polices auto-hébergées (@fontsource) plutôt que Google Fonts : aucune IP envoyée à Google (CNIL)
import '@fontsource/anton/400.css';            // titres
import '@fontsource/inter/400.css';            // texte
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/permanent-marker/400.css'; // annotations "au feutre"
import '@/styles/global.scss';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <SalonProvider>
        <App />
      </SalonProvider>
    </BrowserRouter>
  </StrictMode>,
);
