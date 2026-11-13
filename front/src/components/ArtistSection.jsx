import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import { useSalon } from '../context/SalonContext';
import { assetUrl } from '../api/client';
import { toParagraphs } from '../utils/format';
// Photo de secours, utilisée tant qu'aucune photo n'est envoyée depuis le backoffice
import defaultPhoto from '../assets/images/artist.jpg';
import './ArtistSection.scss';

/**
 * Section "L'artiste" : la marque du salon.
 * Photo et textes viennent du backoffice (menu "L'artiste") via GET /api/artists.
 * La photo est affichée dans ses proportions d'origine (aucun recadrage du visage).
 */
const ArtistSection = () => {
  const { data: artists } = useApi('/artists');
  const { salon } = useSalon();
  // Les styles pratiqués viennent des catégories gérées dans le backoffice
  const { data: categories } = useApi('/categories');

  const artist = artists?.[0];
  if (!artist) return null; // pas encore de fiche (ou chargement en cours)

  const paragraphs = toParagraphs(artist.biography);

  return (
    <section className="artist-section" aria-labelledby="artist-title">
      <div className="container artist-section__inner">
        <div className="artist-section__photo">
          <img
            src={artist.pictureUrl ? assetUrl(artist.pictureUrl) : defaultPhoto}
            alt={`Portrait de ${artist.firstName} ${artist.lastName}, tatoueur de Boogi'ink`}
            loading="lazy"
          />
        </div>

        <div className="artist-section__text">
          <p className="eyebrow">L'artiste derrière Boogi'ink</p>
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

          {categories?.length > 0 && (
            <ul className="artist-section__styles" aria-label="Styles pratiqués">
              {categories.map((category) => (
                <li key={category.id}>
                  <Link to={`/realisations?category=${category.id}`}>{category.name}</Link>
                </li>
              ))}
            </ul>
          )}

          <div className="artist-section__actions">
            <Link to="/realisations" className="btn btn--primary">Voir son travail</Link>
            {salon?.instagramUrl && (
              <a href={salon.instagramUrl} target="_blank" rel="noreferrer" className="btn btn--ghost">Suivre sur Instagram</a>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export default ArtistSection;
