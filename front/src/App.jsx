import { useEffect, useRef } from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router';
import { ToastContainer } from 'react-toastify';

import Header from '@/components/layout/Header';
import Footer from '@/components/layout/Footer';
import MobileBookingBar from '@/components/layout/MobileBookingBar';
import HomePage from '@/pages/HomePage';
import RealisationsPage from '@/pages/RealisationsPage';
import GalleryDetailPage from '@/pages/GalleryDetailPage';
import AtelierPage from '@/pages/AtelierPage';
import FlashsPage from '@/pages/FlashsPage';
import FaqPage from '@/pages/FaqPage';
import ContentPage from '@/pages/ContentPage';
import VisitorBookPage from '@/pages/VisitorBookPage';
import ContactPage from '@/pages/ContactPage';
import NotFoundPage from '@/pages/NotFoundPage';

const App = () => {
  const { pathname, hash } = useLocation();
  const isHome = pathname === '/';

  // Avec un #section, c'est la page qui défile (useScrollToHash)
  useEffect(() => {
    if (!hash) window.scrollTo(0, 0);
  }, [pathname, hash]);

  // Accessibilité : sans rechargement, un lecteur d'écran ne voit pas le changement de page ;
  // on focus le h1 (annoncé), sinon le <main>. Pas au premier affichage.
  const mainRef = useRef(null);
  // Même adresse = premier affichage ou effet rejoué par <StrictMode> : focus inchangé
  const previousPathname = useRef(pathname);
  useEffect(() => {
    if (previousPathname.current === pathname) return;
    previousPathname.current = pathname;
    const target = mainRef.current?.querySelector('h1') ?? mainRef.current;
    if (!target) return;
    target.setAttribute('tabindex', '-1'); // un titre ne reçoit pas le focus sans cela
    target.focus({ preventScroll: true });
  }, [pathname]);

  const skipToContent = (e) => {
    e.preventDefault(); // pas de "#contenu" dans l'URL : le hash sert à viser des sections
    mainRef.current?.focus();
  };

  return (
    <div className="app">
      <a href="#contenu" className="skip-link" onClick={skipToContent}>Aller au contenu</a>

      <ToastContainer position="top-center" autoClose={5000} limit={3} theme="dark" />

      <Header />
      {/* Pas d'offset sur l'accueil : la vidéo passe sous le menu transparent.
          tabIndex -1 : focusable par "Aller au contenu" et au changement de page */}
      <main id="contenu" ref={mainRef} tabIndex={-1} className={isHome ? 'site-main' : 'site-main site-main--offset'}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/realisations" element={<RealisationsPage />} />
          <Route path="/realisations/:id" element={<GalleryDetailPage />} />
          <Route path="/atelier" element={<AtelierPage />} />
          <Route path="/flashs" element={<FlashsPage />} />
          <Route path="/livre-d-or" element={<VisitorBookPage />} />
          <Route path="/faq" element={<FaqPage />} />
          <Route path="/pages/:slug" element={<ContentPage />} />
          <Route path="/contact" element={<ContactPage />} />
          {/* Ancien espace client (supprimé) : redirections pour les vieux liens */}
          <Route path="/connexion" element={<Navigate to="/" replace />} />
          <Route path="/inscription" element={<Navigate to="/" replace />} />
          <Route path="/profil/*" element={<Navigate to="/" replace />} />
          <Route path="/atelier-tatouage" element={<Navigate to="/atelier" replace />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
      <Footer />
      <MobileBookingBar />
    </div>
  );
};

export default App;
