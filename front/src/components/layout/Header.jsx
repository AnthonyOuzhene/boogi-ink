import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation, useNavigate } from 'react-router';
import { FaBars, FaTimes } from 'react-icons/fa';
import { AiOutlineUser } from 'react-icons/ai';
import { toast } from 'react-toastify';
import { useAuth } from '../../context/AuthContext';
import { BACKEND_URL } from '../../api/client';
import logo from '../../assets/images/logo.png';
import './Header.scss';

const LINKS = [
  { to: '/realisations', label: 'Réalisations' },
  { to: '/atelier', label: "L'atelier" },
  { to: '/livre-d-or', label: "Livre d'or" },
  { to: '/contact', label: 'Contact' },
];

const Header = () => {
  const { isConnected, isAdmin, user, logout } = useAuth();
  const navigate = useNavigate();
  const { pathname } = useLocation();

  const [menuOpen, setMenuOpen] = useState(false);       // menu mobile
  const [accountOpen, setAccountOpen] = useState(false); // sous-menu du compte
  const closeMenus = () => {
    setMenuOpen(false);
    setAccountOpen(false);
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

  const handleLogout = () => {
    logout();
    closeMenus();
    toast.info('Vous êtes déconnecté.');
    navigate('/');
  };

  return (
    <header className={`site-header ${overlay ? 'site-header--overlay' : 'site-header--solid'}`}>
      <div className="container site-header__inner">
        <Link to="/" className="brand" onClick={closeMenus}>
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
            <NavLink key={link.to} to={link.to} className="site-nav__link" onClick={closeMenus}>
              {link.label}
            </NavLink>
          ))}

          {isConnected ? (
            <div className="account">
              <button
                type="button"
                className="account__toggle"
                aria-expanded={accountOpen}
                onClick={() => setAccountOpen(!accountOpen)}
              >
                <AiOutlineUser size={20} aria-hidden="true" /> {user.name}
              </button>
              {accountOpen && (
                <ul className="account__menu">
                  <li><Link to="/profil" onClick={closeMenus}>Mon compte</Link></li>
                  {isAdmin && (
                    <li><a href={`${BACKEND_URL}/admin`} target="_blank" rel="noreferrer">Backoffice</a></li>
                  )}
                  <li><button type="button" onClick={handleLogout}>Déconnexion</button></li>
                </ul>
              )}
            </div>
          ) : (
            <NavLink to="/connexion" className="site-nav__link" onClick={closeMenus}>Connexion</NavLink>
          )}

          <Link to="/contact" className="btn btn--primary site-nav__cta" onClick={closeMenus}>
            Prendre rendez-vous
          </Link>
        </nav>
      </div>
    </header>
  );
};

export default Header;
