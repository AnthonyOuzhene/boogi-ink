import { useEffect, useState } from 'react';
import { Link, useLocation } from 'react-router';
import './MobileBookingBar.scss';

// Apparaît après ce défilement (px) : sur l'accueil, à peu près quand la grande photo
// et ses boutons sont passés
const SHOW_AFTER = 500;

/**
 * Barre "Prendre rendez-vous" fixée en bas de l'écran, sur téléphone et tablette uniquement
 * (le bouton du menu y est caché dans le burger). Masquée sur la page Contact, sa destination.
 * L'affichage par taille d'écran se fait en CSS (MobileBookingBar.scss).
 */
const MobileBookingBar = () => {
  const { pathname } = useLocation();
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY > SHOW_AFTER);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  if (pathname === '/contact') return null;

  return (
    // inert : barre cachée = ni cliquable ni atteignable au clavier
    <div className={`booking-bar ${visible ? 'is-visible' : ''}`} inert={!visible}>
      <Link to="/contact" className="btn btn--primary booking-bar__btn">Prendre rendez-vous</Link>
    </div>
  );
};

export default MobileBookingBar;
