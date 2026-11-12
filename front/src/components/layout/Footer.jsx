import { Link } from 'react-router';
import useApi from '../../hooks/useApi';
import { CONTACT_EMAIL, FACEBOOK_URL, INSTAGRAM_URL } from '../../config';
import './Footer.scss';

const Footer = () => {
  // Coordonnées gérées dans le backoffice (menu "Salon")
  const { data: homes } = useApi('/homes');
  const salon = homes?.[0];

  return (
    <footer className="site-footer">
      <div className="container site-footer__inner">
        <div className="site-footer__salon">
          <span className="site-footer__brand">Boogi'<span className="text-cyan">ink</span></span>
          {salon && (
            <>
              <span className="muted">{salon.address}, {salon.zipCode} {salon.city}</span>
              <a href={`tel:${salon.phoneNumber.replace(/\s/g, '')}`} className="muted">{salon.phoneNumber}</a>
            </>
          )}
          <a href={`mailto:${CONTACT_EMAIL}`} className="muted">{CONTACT_EMAIL}</a>
        </div>

        <nav aria-label="Pied de page" className="site-footer__links">
          <Link to="/realisations">Réalisations</Link>
          <Link to="/atelier">L'atelier</Link>
          <Link to="/livre-d-or">Livre d'or</Link>
          <Link to="/#faq">FAQ</Link>
          <Link to="/contact">Contact</Link>
        </nav>

        <div className="site-footer__links">
          <a href={INSTAGRAM_URL} target="_blank" rel="noreferrer">Instagram</a>
          <a href={FACEBOOK_URL} target="_blank" rel="noreferrer">Facebook</a>
          {/* Pages à créer */}
          <span className="muted">Mentions légales</span>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
