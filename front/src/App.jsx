import { useEffect, useRef } from 'react';
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

  // Accessibilité : le site change de page sans recharger le navigateur, un lecteur d'écran
  // ne s'en rend donc pas compte. À chaque nouvelle page, on place le focus sur son titre (h1),
  // qu'il annonce ; à défaut, sur le contenu principal. Pas au premier affichage du site.
  const mainRef = useRef(null);
  // Adresse de la page précédente : même adresse = premier affichage (ou effet rejoué par
  // <StrictMode> en développement), on ne touche pas au focus
  const previousPathname = useRef(pathname);
  useEffect(() => {
    if (previousPathname.current === pathname) return;
    previousPathname.current = pathname;
    const target = mainRef.current?.querySelector('h1') ?? mainRef.current;
    if (!target) return;
    target.setAttribute('tabindex', '-1'); // un titre ne reçoit pas le focus sans cela
    target.focus({ preventScroll: true });
  }, [pathname]);

  // Lien "Aller au contenu" : saute le menu (utile au clavier sur chaque page)
  const skipToContent = (e) => {
    e.preventDefault(); // pas de "#contenu" dans l'URL (le site s'en sert pour viser des sections)
    mainRef.current?.focus();
  };

  return (
    <div className="app">
      <a href="#contenu" className="skip-link" onClick={skipToContent}>Aller au contenu</a>

      {/* Un seul conteneur de notifications pour tout le site */}
      <ToastContainer position="top-center" autoClose={5000} limit={3} theme="dark" />

      <Header />
      {/* Sur l'accueil, la grande photo passe sous le menu transparent.
          tabIndex -1 : peut recevoir le focus (lien "Aller au contenu", changement de page) */}
      <main id="contenu" ref={mainRef} tabIndex={-1} className={isHome ? 'site-main' : 'site-main site-main--offset'}>
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
