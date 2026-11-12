import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router';
import { AuthProvider } from './context/AuthProvider';
import App from './App';
import './styles/global.scss';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    {/* BrowserRouter : gère les URL (/contact, /livre-d-or...) sans recharger la page */}
    <BrowserRouter>
      {/* AuthProvider : rend l'utilisateur connecté accessible partout via useAuth() */}
      <AuthProvider>
        <App />
      </AuthProvider>
    </BrowserRouter>
  </StrictMode>,
);
