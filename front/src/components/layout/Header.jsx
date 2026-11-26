import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router';
import { FaBars, FaTimes } from 'react-icons/fa';
import SocialLinks from '@/components/ui/SocialLinks';
import logo from '@/assets/images/logo.webp';
import './Header.scss';

// Menu volontairement court : Contact est accessible via le bouton "Prendre rendez-vous"
// et le pied de page ; la FAQ via le pied de page, l'accueil, L'atelier et Contact.
const LINKS = [
  { to: '/realisations', label: 'Réalisations' },
  { to: '/flashs', label: 'Flashs' },
  { to: '/atelier', label: "L'atelier" },
  { to: '/livre-d-or', label: "Livre d'or" },
];

const Header = () => {
  const { pathname } = useLocation();

  const [menuOpen, setMenuOpen] = useState(false);
  const closeMenu = () => setMenuOpen(false);

  // Déjà sur l'accueil, l'adresse ne change pas et App.jsx ne remonte pas en haut :
  // on le fait nous-mêmes (en douceur grâce à scroll-behavior dans styles/_base.scss)
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
          {/* Logo animé (contour tracé au chargement, vibration au survol) : voir Header.scss */}
          <span className="brand__mark">
            <img src={logo} alt="" className="brand__logo" />
            <svg className="brand__ring" viewBox="0 0 52 52" aria-hidden="true">
              <circle cx="26" cy="26" r="25" pathLength="100" />
            </svg>
            <span className="brand__needle" aria-hidden="true" />
          </span>
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
          <div className="site-nav__links">
            {LINKS.map((link) => (
              // NavLink ajoute la classe "active" quand on est sur la page correspondante
              <NavLink key={link.to} to={link.to} className="site-nav__link" onClick={closeMenu}>
                {link.label}
              </NavLink>
            ))}
          </div>

          <div className="site-nav__actions">
            {/* Dans le menu burger, les réseaux passent sous le bouton (ordre inversé dans Header.scss) */}
            <SocialLinks className="site-nav__social" />

            <Link to="/contact" className="btn btn--primary site-nav__cta" onClick={closeMenu}>
              Prendre rendez-vous
            </Link>
          </div>
        </nav>
      </div>
    </header>
  );
};

export default Header;
