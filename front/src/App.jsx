import { useEffect } from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router';
import { ToastContainer } from 'react-toastify';

import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import MobileBookingBar from './components/layout/MobileBookingBar';
import HomePage from './pages/HomePage';
import RealisationsPage from './pages/RealisationsPage';
import GalleryDetailPage from './pages/GalleryDetailPage';
import AtelierPage from './pages/AtelierPage';
import FlashsPage from './pages/FlashsPage';
import FaqPage from './pages/FaqPage';
import ContentPage from './pages/ContentPage';
import VisitorBookPage from './pages/VisitorBookPage';
import ContactPage from './pages/ContactPage';
import NotFoundPage from './pages/NotFoundPage';

const App = () => {
  const { pathname, hash } = useLocation();
  const isHome = pathname === '/';

  // Changement de page : on remonte en haut (sinon on garde le défilement de la page précédente).
  // Sauf si l'URL vise une section (/atelier#seance) : la page s'en charge (hook useScrollToHash).
  useEffect(() => {
    if (!hash) window.scrollTo(0, 0);
  }, [pathname, hash]);

  return (
    <div className="app">
      {/* Un seul conteneur de notifications pour tout le site */}
      <ToastContainer position="top-center" autoClose={5000} limit={3} theme="dark" />

      <Header />
      {/* Sur l'accueil, la grande photo passe sous le menu transparent */}
      <main className={isHome ? 'site-main' : 'site-main site-main--offset'}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/realisations" element={<RealisationsPage />} />
          <Route path="/realisations/:id" element={<GalleryDetailPage />} />
          <Route path="/atelier" element={<AtelierPage />} />
          <Route path="/flashs" element={<FlashsPage />} />
          <Route path="/livre-d-or" element={<VisitorBookPage />} />
          <Route path="/faq" element={<FaqPage />} />
          {/* Pages gérées dans le backoffice : mentions légales, confidentialité… */}
          <Route path="/pages/:slug" element={<ContentPage />} />
          <Route path="/contact" element={<ContactPage />} />
          {/* Ancien espace client (supprimé) : les anciennes adresses ramènent à l'accueil */}
          <Route path="/connexion" element={<Navigate to="/" replace />} />
          <Route path="/inscription" element={<Navigate to="/" replace />} />
          <Route path="/profil/*" element={<Navigate to="/" replace />} />
          {/* Ancienne adresse de la page de l'atelier */}
          <Route path="/atelier-tatouage" element={<Navigate to="/atelier" replace />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
      <Footer />
      {/* Téléphone et tablette : bouton "Prendre rendez-vous" toujours à portée de pouce */}
      <MobileBookingBar />
    </div>
  );
};

export default App;
