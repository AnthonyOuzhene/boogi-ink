import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router';
import { FaBars, FaTimes } from 'react-icons/fa';
import SocialLinks from '../SocialLinks';
import logo from '../../assets/images/logo.png';
import './Header.scss';

// Menu volontairement court. Les autres pages restent accessibles ailleurs :
// - Contact : bouton "Prendre rendez-vous" (même page) et pied de page
// - FAQ : pied de page, aperçu sur l'accueil, L'atelier (« Une séance ») et page Contact
// (Pas d'espace client : le site n'a ni connexion ni compte pour les visiteurs.)
const LINKS = [
  { to: '/realisations', label: 'Réalisations' },
  { to: '/flashs', label: 'Flashs' },
  { to: '/atelier', label: "L'atelier" },
  { to: '/livre-d-or', label: "Livre d'or" },
];

const Header = () => {
  const { pathname } = useLocation();

  const [menuOpen, setMenuOpen] = useState(false); // menu mobile
  const closeMenu = () => setMenuOpen(false);

  // Logo : depuis une autre page, le lien mène à l'accueil (et App.jsx remonte en haut).
  // Déjà sur l'accueil, l'adresse ne change pas : on remonte nous-mêmes, en douceur
  // (scroll-behavior: smooth dans global.scss)
  const handleBrandClick = () => {
    closeMenu();
    if (pathname === '/') window.scrollTo({ top: 0 });
  };

  // Sur l'accueil, le menu est transparent au-dessus de la photo, puis devient opaque au défilement
  const [scrolled, setScrolled] = useState(false);
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 80);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);
  const overlay = pathname === '/' && !scrolled && !menuOpen;

  return (
    <header className={`site-header ${overlay ? 'site-header--overlay' : 'site-header--solid'}`}>
      <div className="container site-header__inner">
        <Link to="/" className="brand" onClick={handleBrandClick}>
          <img src={logo} alt="" className="brand__logo" />
          <span className="brand__name">Boogi'ink</span>
        </Link>

        <button
          type="button"
          className="site-header__burger"
          aria-label={menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'}
          aria-expanded={menuOpen}
          onClick={() => setMenuOpen(!menuOpen)}
        >
          {menuOpen ? <FaTimes size={26} /> : <FaBars size={26} />}
        </button>

        <nav aria-label="Navigation principale" className={`site-nav ${menuOpen ? 'is-open' : ''}`}>
          {LINKS.map((link) => (
            // NavLink ajoute la classe "active" quand on est sur la page correspondante
            <NavLink key={link.to} to={link.to} className="site-nav__link" onClick={closeMenu}>
              {link.label}
            </NavLink>
          ))}

          {/* Réseaux sociaux (Instagram = la vitrine de l'artiste) : petites icônes avant le bouton ;
              dans le menu burger, elles passent en bas, sous le bouton de rendez-vous (Header.scss) */}
          <SocialLinks className="site-nav__social" />

          <Link to="/contact" className="btn btn--primary site-nav__cta" onClick={closeMenu}>
            Prendre rendez-vous
          </Link>
        </nav>
      </div>
    </header>
  );
};

export default Header;
