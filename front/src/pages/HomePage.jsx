import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import CategoryBand from '../components/CategoryBand';
import GalleryGrid from '../components/GalleryGrid';
import ServiceList from '../components/ServiceList';
import ArtistSection from '../components/ArtistSection';
import FaqSection from '../components/FaqSection';
import banner from '../assets/images/banner.jpg';
import './pages.scss';
import './HomePage.scss';

const HomePage = () => {
  const { data: homes } = useApi('/homes');
  const { data: galleries } = useApi('/galleries');
  const { data: services } = useApi('/services');
  const { data: comments } = useApi('/comments');

  const city = homes?.[0]?.city;
  const latestComment = comments?.[0];

  return (
    <>
      {/* --- Grande photo d'accueil --- */}
      <section className="hero">
        <img src={banner} alt="" className="hero__img" />
        <div className="hero__tint" aria-hidden="true" />
        <div className="container hero__content">
          <p className="eyebrow">Salon de tatouage{city && ` — ${city}`}</p>
          <h1 className="hero__title display">
            L'encre qui<br />
            <span className="glow">vous ressemble</span>
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

      {/* --- Dernier avis du livre d'or --- */}
      {latestComment && (
        <section className="container section">
          <figure className="neon-card quote">
            <span className="quote__mark" aria-hidden="true">“</span>
            <blockquote className="quote__text">{latestComment.message}</blockquote>
            <figcaption className="quote__footer">
              <span className="muted">
                {latestComment.author.name}
                {latestComment.projectName && ` — ${latestComment.projectName}`}
              </span>
              <Link to="/livre-d-or" className="link-arrow">Lire le livre d'or →</Link>
            </figcaption>
          </figure>
        </section>
      )}
    </>
  );
};

export default HomePage;
