import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import useMediaQuery from '../hooks/useMediaQuery';
import { useSalon } from '../context/SalonContext';
import SalonCards from '../components/SalonCards';
import GalleryGrid from '../components/GalleryGrid';
import ArtistSection from '../components/ArtistSection';
import Scribble from '../components/Scribble';
import FaqSection from '../components/FaqSection';
import SessionSteps from '../components/SessionSteps';
import { FlashGrid } from '../components/FlashGallery';
import GuestbookCarousel from '../components/GuestbookCarousel';
// Vidéo du hero (fichier fixe, sans son) + photo extraite de la vidéo : affichée pendant
// le chargement, et à la place de la vidéo si "réduire les animations" est activé
import heroVideo from '../assets/videos/hero-tattoo.mp4';
import heroPoster from '../assets/images/hero-tattoo-poster.jpg';
import './pages.scss';
import './HomePage.scss';

const HomePage = () => {
  const { salon, loading: salonLoading } = useSalon();
  // Réglage système (accessibilité) : pas de vidéo qui bouge, la photo fixe à la place
  const reduceMotion = useMediaQuery('(prefers-reduced-motion: reduce)');
  const { data: galleries } = useApi('/galleries');
  const { data: artists } = useApi('/artists');
  const { data: flashes } = useApi('/flashes');
  const { data: steps } = useApi('/session_steps');
  const { data: categories } = useApi('/categories');
  // Styles affichés au-dessus des réalisations : seulement ceux qui ont au moins une réalisation
  const styles = categories?.filter((category) => category.galleryCount > 0) ?? [];

  // Aperçu de 4 flashs. L'API liste les disponibles en premier : s'il y en a moins de 4,
  // les réservés complètent la planche (avec leur tampon), ils montrent quand même le style.
  // On prend le début de la liste : chaque flash garde son numéro de la planche complète.
  const previewFlashes = flashes?.slice(0, 4) ?? [];

  return (
    <>
      {/* --- Vidéo d'accueil (décorative : ignorée par les lecteurs d'écran) --- */}
      <section className="hero">
        {reduceMotion ? (
          <img src={heroPoster} alt="" className="hero__img" />
        ) : (
          // Lecture automatique autorisée par les navigateurs seulement si la vidéo est muette (muted) ;
          // playsInline : sur iPhone, reste dans la page au lieu de passer en plein écran.
          // poster : photo affichée le temps que la vidéo se charge
          <video
            className="hero__img"
            src={heroVideo}
            poster={heroPoster}
            autoPlay
            muted
            loop
            playsInline
            preload="auto"
            aria-hidden="true"
          />
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
                  <span className="hero__ink glow">{salon?.heroHighlight ?? 'te ressemble'}</span>
                  <span className="hero__needle" aria-hidden="true" />
                  <span className="hero__underline" aria-hidden="true" />
                </span>
              </>
            )}
          </h1>
          <div className="hero__actions">
            <Link to="/contact" className="btn btn--primary">Parle-moi de ton projet</Link>
            <Link to="/realisations" className="btn btn--ghost">Voir les réalisations</Link>
            {/* Annotation au feutre (masquée sur téléphone, où les boutons sont empilés) */}
            <Scribble arrow="left" className="hero__scribble">c'est par ici !</Scribble>
          </div>
        </div>
      </section>

      {/* Cartes à jouer + artiste sur un même fond bleuté (pas de bandeau noir sous les cartes).
          Le fond commence au bas de la vidéo : le haut des cartes reste posé dessus. */}
      <div className="home-intro">
        <SalonCards />

        {/* --- L'artiste : version courte, son parcours complet est sur L'atelier --- */}
        <ArtistSection artist={artists?.[0]} />
      </div>

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
                <Link key={category.id} to={`/realisations?category=${category.slug}`}>{category.name}</Link>
              ))}
            </nav>
          )}
          <GalleryGrid galleries={galleries.slice(0, 5)} />
        </section>
      )}

      {/* --- Flashs disponibles : aperçu, la liste complète est sur /flashs ---
          Bandeau "papier" (fond clair) : les dessins sont sur papier blanc, ils s'y fondent */}
      {previewFlashes.length > 0 && (
        <section className="band paper">
          <div className="container">
            <div className="section-head">
              <div className="stack">
                <p className="eyebrow">Prêts à tatouer</p>
                <h2 className="section-title">Flashs <span className="text-cyan">disponibles</span></h2>
                <p className="muted lead-text">Des motifs dessinés par {artists?.[0]?.firstName ?? "l'artiste"}, tatoués une seule fois.</p>
                <Scribble arrow="down">premier arrivé, premier servi !</Scribble>
              </div>
              <Link to="/flashs" className="link-arrow">Tous les flashs →</Link>
            </div>
            <FlashGrid flashes={previewFlashes} />
          </div>
        </section>
      )}

      {/* --- Comment ça se passe : aperçu du déroulé, le détail est sur L'atelier --- */}
      {steps?.length > 0 && (
        <section className="band">
          <div className="container">
            <div className="band__head">
              <p className="eyebrow">De l'idée à la cicatrisation</p>
              <h2 className="section-title section-title--small">Comment ça <span className="text-cyan">se passe</span></h2>
            </div>
            <SessionSteps steps={steps.slice(0, 5)} variant="preview" />
            <p className="band__more">
              <Link to="/atelier#seance" className="link-arrow">Toutes les étapes en détail →</Link>
            </p>
          </div>
        </section>
      )}

      {/* --- Questions fréquentes --- */}
      <FaqSection />

      {/* --- Livre d'or : carrousel des avis publiés, sur papier --- */}
      <GuestbookCarousel className="paper" />
    </>
  );
};

export default HomePage;
