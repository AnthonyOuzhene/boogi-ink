import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import { useSalon } from '../context/SalonContext';
import { assetUrl } from '../api/client';
import ArgumentsBand from '../components/ArgumentsBand';
import GalleryGrid from '../components/GalleryGrid';
import ServiceList from '../components/ServiceList';
import ArtistSection from '../components/ArtistSection';
import FaqSection from '../components/FaqSection';
import SessionSteps from '../components/SessionSteps';
import { FlashGrid } from '../components/FlashGallery';
import GuestbookCarousel from '../components/GuestbookCarousel';
// Photo de secours tant qu'aucune n'est envoyée depuis le backoffice
import defaultBanner from '../assets/images/banner.jpg';
import './pages.scss';
import './HomePage.scss';

const HomePage = () => {
  const { salon, loading: salonLoading } = useSalon();
  const { data: galleries } = useApi('/galleries');
  const { data: services } = useApi('/services');
  const { data: artists } = useApi('/artists');
  const { data: flashes } = useApi('/flashes');
  const { data: steps } = useApi('/session_steps');
  const { data: categories } = useApi('/categories');
  // Styles affichés au-dessus des réalisations : seulement ceux qui ont au moins une réalisation
  const styles = categories?.filter((category) => category.galleryCount > 0) ?? [];

  // L'API liste les flashs disponibles en premier : l'aperçu n'affiche que ceux-là
  const availableFlashes = flashes?.filter((flash) => flash.isAvailable).slice(0, 4) ?? [];

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

      <ArgumentsBand />

      {/* --- L'artiste : version courte, son parcours complet est sur L'atelier --- */}
      <ArtistSection artist={artists?.[0]} />

      {/* --- Dernières réalisations --- */}
      {galleries?.length > 0 && (
        <section className="container section">
          <div className="section-head">
            <h2 className="section-title">Réali<span className="text-cyan">sations</span></h2>
            <Link to="/realisations" className="link-arrow">Toute la galerie →</Link>
          </div>
          {/* Les styles du tatoueur, juste au-dessus des photos : chacun ouvre la galerie filtrée */}
          {styles.length > 0 && (
            <nav aria-label="Styles de tatouage" className="filters home-styles">
              {styles.map((category) => (
                <Link key={category.id} to={`/realisations?category=${category.id}`}>{category.name}</Link>
              ))}
            </nav>
          )}
          <GalleryGrid galleries={galleries.slice(0, 5)} />
        </section>
      )}

      {/* --- Flashs disponibles : aperçu, la liste complète est sur /flashs ---
          Bandeau "papier" (fond clair) : les dessins sont sur papier blanc, ils s'y fondent */}
      {availableFlashes.length > 0 && (
        <section className="band paper">
          <div className="container">
            <div className="section-head">
              <div className="stack">
                <p className="eyebrow">Prêts à tatouer</p>
                <h2 className="section-title">Flashs <span className="text-cyan">disponibles</span></h2>
                <p className="muted lead-text">Des motifs dessinés par {artists?.[0]?.firstName ?? "l'artiste"}, tatoués une seule fois. Premier arrivé, premier servi.</p>
              </div>
              <Link to="/flashs" className="link-arrow">Tous les flashs →</Link>
            </div>
            <FlashGrid flashes={availableFlashes} />
          </div>
        </section>
      )}

      {/* --- Comment ça se passe : aperçu du déroulé, le détail est sur L'atelier --- */}
      {steps?.length > 0 && (
        <section className="band">
          <div className="container">
            <div className="band__head">
              <p className="eyebrow">De l'idée à la cicatrisation</p>
              <h2 className="section-title">Comment ça <span className="text-cyan">se passe</span></h2>
            </div>
            <SessionSteps steps={steps.slice(0, 5)} variant="preview" />
            <p className="band__more">
              <Link to="/atelier#seance" className="link-arrow">Toutes les étapes en détail →</Link>
            </p>
          </div>
        </section>
      )}

      {/* --- Prestations : titre à gauche, 3 tarifs à droite (sur papier, comme une carte de tarifs) --- */}
      <section className="band paper">
        <div className="container split">
          <div className="split__intro">
            <h2 className="section-title">Presta<span className="text-cyan">tions</span></h2>
            <p className="muted">Chaque projet est dessiné sur mesure. Les grandes pièces et les recouvrements sont établis sur devis.</p>
            <Link to="/atelier#tarifs" className="link-arrow">Tous les tarifs →</Link>
          </div>
          <div className="split__list">
            {services && <ServiceList services={services.slice(0, 3)} />}
          </div>
        </div>
      </section>

      {/* --- Questions fréquentes --- */}
      <FaqSection />

      {/* --- Livre d'or : carrousel des avis publiés, sur papier --- */}
      <GuestbookCarousel className="paper" />
    </>
  );
};

export default HomePage;
