import { useRef, useState } from 'react';
import { Link } from 'react-router';
import { FaPause, FaPlay } from 'react-icons/fa';
import useApi from '@/hooks/useApi';
import useMediaQuery from '@/hooks/useMediaQuery';
import usePageTitle from '@/hooks/usePageTitle';
import { useSalon } from '@/context/SalonContext';
import SalonCards from '@/components/salon/SalonCards';
import GalleryGrid from '@/components/gallery/GalleryGrid';
import ArtistSection from '@/components/salon/ArtistSection';
import Scribble from '@/components/ui/Scribble';
import FaqSection from '@/components/faq/FaqSection';
import SessionSteps from '@/components/salon/SessionSteps';
import { FlashGrid } from '@/components/flash/FlashGallery';
import GuestbookCarousel from '@/components/guestbook/GuestbookCarousel';
import InstagramFeed from '@/components/salon/InstagramFeed';
// Poster = image extraite de la vidéo : pendant le chargement et si "réduire les animations"
import heroVideo from '@/assets/videos/hero-tattoo.mp4';
import heroPoster from '@/assets/images/hero-tattoo-poster.jpg';
import './HomePage.scss';

const HomePage = () => {
  usePageTitle(null); // "Boogi'ink — Salon de tatouage"
  const { salon, loading: salonLoading } = useSalon();
  const reduceMotion = useMediaQuery('(prefers-reduced-motion: reduce)');

  // WCAG 2.2.2 : un contenu animé > 5 s qui démarre seul doit pouvoir être mis en pause
  const videoRef = useRef(null);
  const [videoPaused, setVideoPaused] = useState(false);
  const toggleVideo = () => {
    const video = videoRef.current;
    if (!video) return;
    if (video.paused) video.play();
    else video.pause();
    setVideoPaused(!video.paused);
  };
  const { data: galleries } = useApi('/galleries');
  const { data: artists } = useApi('/artists');
  const { data: flashes } = useApi('/flashes');
  const { data: steps } = useApi('/session_steps');
  const { data: categories } = useApi('/categories');
  const styles = categories?.filter((category) => category.galleryCount > 0) ?? [];

  // L'API liste les disponibles d'abord : des réservés complètent si besoin.
  // slice depuis le début : chaque flash garde son numéro de la planche complète.
  const previewFlashes = flashes?.slice(0, 4) ?? [];

  return (
    <>
      <section className="hero">
        {reduceMotion ? (
          <img src={heroPoster} alt="" className="hero__img" />
        ) : (
          // muted : condition pour la lecture automatique ; playsInline : sur iPhone, reste dans la page
          <video
            ref={videoRef}
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
          {/* Accroche éditable (backoffice, menu "Salon"). Rendue après le chargement du salon
              pour que l'animation "tracé d'aiguille" (HomePage.scss) parte au bon moment. */}
          <h1 className="hero__title display">
            {!salonLoading && (
              <>
                <span className="hero__line">
                  <span className="hero__ink">{salon?.heroTitle ?? "L'encre qui"}</span>
                  <span className="hero__needle" aria-hidden="true" />
                </span>
                {/* Espace lu par les lecteurs d'écran (invisible : lignes empilées) */}
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
            <Scribble arrow="left" className="hero__scribble">c'est par ici !</Scribble>
            {!reduceMotion && (
              <button
                type="button"
                className="hero__pause"
                aria-label={videoPaused ? 'Relancer la vidéo' : 'Mettre la vidéo en pause'}
                title={videoPaused ? 'Relancer la vidéo' : 'Mettre la vidéo en pause'}
                onClick={toggleVideo}
              >
                {videoPaused ? <FaPlay aria-hidden="true" /> : <FaPause aria-hidden="true" />}
              </button>
            )}
          </div>
        </div>
      </section>

      {/* Wrapper commun : un seul fond bleuté sous cartes + artiste, sans bandeau noir entre les deux */}
      <div className="home-intro">
        <SalonCards />

        <ArtistSection artist={artists?.[0]} />
      </div>

      {galleries?.length > 0 && (
        <section className="container section">
          <div className="section-head">
            <h2 className="section-title">Réali<span className="text-cyan">sations</span></h2>
            <Link to="/realisations" className="link-arrow">Toute la galerie →</Link>
          </div>
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

      {/* Fond papier : les dessins (fond blanc) s'y fondent */}
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

      <FaqSection />

      {/* Posts récupérés par le back ; ne rend rien si aucun post */}
      <InstagramFeed />

      <GuestbookCarousel className="paper" />
    </>
  );
};

export default HomePage;
