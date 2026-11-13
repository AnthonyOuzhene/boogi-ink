import { useEffect } from 'react';
import { Navigate, Route, Routes, useLocation } from 'react-router';
import { ToastContainer } from 'react-toastify';

import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import HomePage from './pages/HomePage';
import RealisationsPage from './pages/RealisationsPage';
import GalleryDetailPage from './pages/GalleryDetailPage';
import AtelierPage from './pages/AtelierPage';
import FaqPage from './pages/FaqPage';
import ContentPage from './pages/ContentPage';
import VisitorBookPage from './pages/VisitorBookPage';
import ContactPage from './pages/ContactPage';
import LoginPage from './pages/LoginPage';
import SignUpPage from './pages/SignUpPage';
import AccountLayout from './pages/account/AccountLayout';
import AccountInfo from './pages/account/AccountInfo';
import AccountComments from './pages/account/AccountComments';
import AccountSecurity from './pages/account/AccountSecurity';
import NotFoundPage from './pages/NotFoundPage';

const App = () => {
  const { pathname } = useLocation();
  const isHome = pathname === '/';

  // Changement de page : on remonte en haut (sinon on garde le défilement de la page précédente)
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

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
          <Route path="/faq" element={<FaqPage />} />
          {/* Pages gérées dans le backoffice : mentions légales, confidentialité… */}
          <Route path="/pages/:slug" element={<ContentPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/connexion" element={<LoginPage />} />
          <Route path="/inscription" element={<SignUpPage />} />
          {/* Espace client : routes imbriquées, affichées dans le <Outlet /> du gabarit */}
          <Route path="/profil" element={<AccountLayout />}>
            <Route index element={<AccountInfo />} />
            <Route path="avis" element={<AccountComments />} />
            <Route path="securite" element={<AccountSecurity />} />
          </Route>
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
