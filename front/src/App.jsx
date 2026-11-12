import { useEffect } from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router';
import { ToastContainer } from 'react-toastify';

import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import HomePage from './pages/HomePage';
import RealisationsPage from './pages/RealisationsPage';
import GalleryDetailPage from './pages/GalleryDetailPage';
import AtelierPage from './pages/AtelierPage';
import VisitorBookPage from './pages/VisitorBookPage';
import ContactPage from './pages/ContactPage';
import LoginPage from './pages/LoginPage';
import SignUpPage from './pages/SignUpPage';
import AccountPage from './pages/AccountPage';
import NotFoundPage from './pages/NotFoundPage';

const App = () => {
  const { pathname, hash } = useLocation();
  const isHome = pathname === '/';

  // Changement de page : on remonte en haut (sinon on garde le défilement de la page précédente).
  // Avec une ancre (/#faq), on descend jusqu'à la section. Elle peut n'apparaître qu'après
  // la réponse de l'API : on la cherche toutes les 100 ms pendant 2 secondes maximum.
  useEffect(() => {
    if (!hash) {
      window.scrollTo(0, 0);
      return undefined;
    }
    let attempts = 0;
    const timer = setInterval(() => {
      const target = document.getElementById(hash.slice(1));
      attempts += 1;
      if (target || attempts > 20) {
        clearInterval(timer);
        target?.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
    return () => clearInterval(timer);
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
          <Route path="/livre-d-or" element={<VisitorBookPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/connexion" element={<LoginPage />} />
          <Route path="/inscription" element={<SignUpPage />} />
          <Route path="/profil" element={<AccountPage />} />
          {/* Ancienne adresse de la page de l'atelier */}
          <Route path="/atelier-tatouage" element={<Navigate to="/atelier" replace />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
      <Footer />
    </div>
  );
};

export default App;
