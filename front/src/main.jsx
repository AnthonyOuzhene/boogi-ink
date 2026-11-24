import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router';
import { SalonProvider } from './context/SalonProvider';
import App from './App';
// Polices hébergées avec le site (paquets @fontsource) et non chargées depuis Google Fonts :
// aucune adresse IP de visiteur n'est envoyée à Google (recommandation CNIL).
// Chaque fichier ne télécharge que les caractères utilisés (latin, accents…).
import '@fontsource/anton/400.css';            // titres
import '@fontsource/inter/400.css';            // texte
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/permanent-marker/400.css'; // annotations "au feutre"
import './styles/global.scss';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    {/* BrowserRouter : gère les URL (/contact, /livre-d-or...) sans recharger la page */}
    <BrowserRouter>
      {/* SalonProvider : coordonnées, horaires et réseaux du salon, chargés une seule fois */}
      <SalonProvider>
        <App />
      </SalonProvider>
    </BrowserRouter>
  </StrictMode>,
);
