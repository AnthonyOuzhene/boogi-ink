import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router';
import { SalonProvider } from './context/SalonProvider';
import App from './App';
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
