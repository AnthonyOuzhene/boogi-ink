import { Link } from 'react-router';
import useApi from '../../hooks/useApi';
import { useSalon } from '../../context/SalonContext';
import { toLines } from '../../utils/format';
import './Footer.scss';

const Footer = () => {
  // Coordonnées, horaires et réseaux : backoffice, menu "Salon"
  const { salon } = useSalon();
  // Pages légales : backoffice, menu "Pages" (titre + adresse uniquement)
  const { data: pages } = useApi('/pages');

  return (
    <footer className="site-footer">
      <div className="container site-footer__inner">
        <div className="site-footer__salon">
          <span className="site-footer__brand">Boogi'<span className="text-cyan">ink</span></span>
          {salon && (
            <>
              <span className="muted">{salon.address}, {salon.zipCode} {salon.city}</span>
              <a href={`tel:${salon.phoneNumber.replace(/\s/g, '')}`} className="muted">{salon.phoneNumber}</a>
              {salon.email && <a href={`mailto:${salon.email}`} className="muted">{salon.email}</a>}
            </>
          )}
        </div>

        {salon?.openingHours && (
          <div className="site-footer__hours">
            <span className="site-footer__label">Horaires</span>
            {toLines(salon.openingHours).map((line) => <span key={line} className="muted">{line}</span>)}
          </div>
        )}

        <nav aria-label="Pied de page" className="site-footer__links">
          <Link to="/realisations">Réalisations</Link>
          <Link to="/atelier">L'atelier</Link>
          <Link to="/livre-d-or">Livre d'or</Link>
          <Link to="/faq">FAQ</Link>
          <Link to="/contact">Contact</Link>
        </nav>

        <div className="site-footer__links">
          {salon?.instagramUrl && <a href={salon.instagramUrl} target="_blank" rel="noreferrer">Instagram</a>}
          {salon?.facebookUrl && <a href={salon.facebookUrl} target="_blank" rel="noreferrer">Facebook</a>}
          {pages?.map((page) => (
            <Link key={page.slug} to={`/pages/${page.slug}`} className="muted">{page.title}</Link>
          ))}
        </div>
      </div>
    </footer>
  );
};

export default Footer;
