import { Link } from 'react-router';
import { FaInstagram } from 'react-icons/fa';
import { useSalon } from '@/context/SalonContext';
import { assetUrl } from '@/api/client';
import { toParagraphs } from '@/utils/format';
import Scribble from '@/components/ui/Scribble';
// Photo de secours tant qu'aucune n'est envoyée depuis le backoffice
import defaultPhoto from '@/assets/images/artist.jpg';
import './ArtistSection.scss';

/**
 * "artist" : fiche chargée par la page (GET /api/artists).
 * - variant="short" (accueil) : citation + présentation courte
 * - variant="full" (L'atelier) : biographie complète + influences ; children en dessous (frise du parcours)
 */
const ArtistSection = ({ artist, variant = 'short', id, eyebrow = "L'artiste derrière Boogi'ink", children }) => {
  const { salon } = useSalon();

  if (!artist) return null; // pas encore de fiche (ou chargement en cours)

  const isFull = variant === 'full';
  const pictureUrl = (isFull && artist.atelierPictureUrl) || artist.pictureUrl;
  // Version courte sans présentation courte : 1er paragraphe de la biographie
  const paragraphs = isFull ? toParagraphs(artist.biography) : toParagraphs(artist.shortBio ?? artist.biography).slice(0, 1);

  return (
    <section id={id} className={`artist-section artist-section--${variant} anchor`} aria-labelledby="artist-title">
      <div className="container">
        <div className="artist-section__inner">
          <div className="artist-section__photo">
            <img
              src={pictureUrl ? assetUrl(pictureUrl) : defaultPhoto}
              alt={`Portrait de ${artist.firstName} ${artist.lastName}, tatoueur de Boogi'ink`}
              loading="lazy"
            />
            {!isFull && <Scribble arrow="down-left" className="artist-section__scribble">c'est moi !</Scribble>}
          </div>

          <div className="artist-section__text">
            <p className={`eyebrow ${isFull ? 'eyebrow--pink' : ''}`}>{eyebrow}</p>
            <h2 id="artist-title" className="artist-section__name">
              {artist.firstName} <span className="glow">{artist.lastName}</span>
            </h2>

            {artist.quote && <blockquote className="artist-section__quote">« {artist.quote} »</blockquote>}

            {paragraphs.length > 0 && (
              <div className="artist-section__bio">
                {paragraphs.map((paragraph) => (
                  // white-space: pre-line (CSS) conserve les simples retours à la ligne
                  <p key={paragraph}>{paragraph}</p>
                ))}
              </div>
            )}

            {isFull && artist.influences?.length > 0 && (
              <div className="artist-section__influences">
                <span className="artist-section__label">Ses influences</span>
                <ul className="chips">
                  {artist.influences.map((influence) => <li key={influence} className="chip">{influence}</li>)}
                </ul>
              </div>
            )}

            <div className="artist-section__actions">
              {isFull
                ? <Link to="/realisations" className="btn btn--primary">Voir son travail</Link>
                // Le hash #artiste fait défiler L'atelier jusqu'à sa section (voir useScrollToHash)
                : <Link to="/atelier#artiste" className="btn btn--primary">Découvrir son parcours</Link>}
              {salon?.instagramUrl && (
                <a href={salon.instagramUrl} target="_blank" rel="noreferrer" className="btn btn--ghost">
                  <FaInstagram size={20} aria-hidden="true" /> Suivre sur Instagram
                </a>
              )}
            </div>
          </div>
        </div>

        {children && <div className="artist-section__extra">{children}</div>}
      </div>
    </section>
  );
};

export default ArtistSection;
