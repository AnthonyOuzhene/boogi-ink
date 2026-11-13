import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import { useSalon } from '../context/SalonContext';
import { assetUrl } from '../api/client';
import CategoryBand from '../components/CategoryBand';
import GalleryGrid from '../components/GalleryGrid';
import ServiceList from '../components/ServiceList';
import ArtistSection from '../components/ArtistSection';
import FaqSection from '../components/FaqSection';
import GuestbookCarousel from '../components/GuestbookCarousel';
// Photo de secours tant qu'aucune n'est envoyée depuis le backoffice
import defaultBanner from '../assets/images/banner.jpg';
import './pages.scss';
import './HomePage.scss';

const HomePage = () => {
  const { salon, loading: salonLoading } = useSalon();
  const { data: galleries } = useApi('/galleries');
  const { data: services } = useApi('/services');


  return (
    <>
      {/* --- Grande photo d'accueil --- */}
      <section className="hero">
        {/* Pas d'image tant que la fiche charge : évite d'afficher la photo par défaut puis de la remplacer */}
        {!salonLoading && (
          <img src={salon?.pictureUrl ? assetUrl(salon.pictureUrl) : defaultBanner} alt="" className="hero__img" />
        )}
        <div className="hero__tint" aria-hidden="true" />
        <div className="container hero__content">
          <p className="eyebrow">Salon de tatouage{salon?.city && ` — ${salon.city}`}</p>
          {/* Accroche : backoffice, menu "Salon" ; textes par défaut si les champs sont vides.
              Animation "tracé d'aiguille" : chaque ligne est dessinée de gauche à droite par une
              aiguille lumineuse (CSS dans HomePage.scss). Elle démarre quand le titre apparaît,
              c'est-à-dire une fois la fiche du salon chargée. */}
          <h1 className="hero__title display">
            {!salonLoading && (
              <>
                <span className="hero__line">
                  <span className="hero__ink">{salon?.heroTitle ?? "L'encre qui"}</span>
                  <span className="hero__needle" aria-hidden="true" />
                </span>
                {/* Espace entre les deux lignes pour les lecteurs d'écran (invisible : lignes empilées) */}
                {' '}
                <span className="hero__line hero__line--highlight">
                  <span className="hero__ink glow">{salon?.heroHighlight ?? 'vous ressemble'}</span>
                  <span className="hero__needle" aria-hidden="true" />
                  <span className="hero__underline" aria-hidden="true" />
                </span>
              </>
            )}
          </h1>
          <div className="hero__actions">
            <Link to="/contact" className="btn btn--primary">Parlez-nous de votre projet</Link>
            <Link to="/realisations" className="btn btn--ghost">Voir les réalisations</Link>
          </div>
        </div>
      </section>

      <CategoryBand />

      {/* --- L'artiste : la marque du salon --- */}
      <ArtistSection />

      {/* --- Dernières réalisations --- */}
      {galleries?.length > 0 && (
        <section className="container section">
          <div className="section-head">
            <h2 className="section-title">Réali<span className="text-cyan">sations</span></h2>
            <Link to="/realisations" className="link-arrow">Toute la galerie →</Link>
          </div>
          <GalleryGrid galleries={galleries.slice(0, 5)} />
        </section>
      )}

      {/* --- Prestations : titre à gauche, tarifs à droite --- */}
      <section className="container section split">
        <div className="split__intro">
          <h2 className="section-title">Presta<span className="text-cyan">tions</span></h2>
          <p className="muted">Chaque projet est dessiné sur mesure. Les grandes pièces et les recouvrements sont établis sur devis.</p>
          <Link to="/atelier" className="link-arrow">Toutes les prestations →</Link>
        </div>
        <div className="split__list">
          {services && <ServiceList services={services.slice(0, 5)} />}
        </div>
      </section>

      {/* --- Questions fréquentes --- */}
      <FaqSection />

      {/* --- Livre d'or : carrousel des avis publiés --- */}
      <GuestbookCarousel />
    </>
  );
};

export default HomePage;
