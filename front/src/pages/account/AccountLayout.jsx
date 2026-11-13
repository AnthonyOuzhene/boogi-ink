import { Navigate, NavLink, Outlet } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import PageHero from '../../components/PageHero';
import './AccountLayout.scss';

// Sections de l'espace client ; "end" : /profil n'est actif QUE sur /profil (pas sur /profil/avis)
const SECTIONS = [
  { to: '/profil', label: 'Mes informations', end: true },
  { to: '/profil/avis', label: 'Mes avis' },
  { to: '/profil/favoris', label: 'Mes favoris' },
  { to: '/profil/securite', label: 'Sécurité' },
];

/**
 * Gabarit de l'espace client : titre + menu + contenu de la section choisie.
 * Les sous-pages (routes imbriquées dans App.jsx) s'affichent à la place de <Outlet />.
 * La protection est faite ICI, une seule fois, pour toutes les sous-pages.
 */
const AccountLayout = () => {
  const { user, loading } = useAuth();

  // Au chargement de la page, on attend de savoir si le jeton est encore valide
  if (loading) return null;
  // Espace réservé : sans connexion, redirection vers /connexion
  if (!user) return <Navigate to="/connexion" replace />;

  return (
    <>
      <PageHero eyebrow="Mon compte" title={<>Bonjour <span className="text-cyan">{user.name}</span></>} />

      <div className="container section section--tight account-layout">
        <nav aria-label="Mon compte" className="account-nav">
          {SECTIONS.map((section) => (
            <NavLink key={section.to} to={section.to} end={section.end} className="account-nav__link">
              {section.label}
            </NavLink>
          ))}
        </nav>

        <div className="account-layout__content">
          <Outlet />
        </div>
      </div>
    </>
  );
};

export default AccountLayout;
