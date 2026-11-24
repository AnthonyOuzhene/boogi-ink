import { useState } from 'react';
import { Link } from 'react-router';
import { FaPlay } from 'react-icons/fa';
import { assetUrl } from '../api/client';
import useMediaQuery from '../hooks/useMediaQuery';
import Scribble from './Scribble';
import './GalleryGrid.scss';

/** Lien par défaut (accueil) : la page Réalisations, visionneuse ouverte sur ce tatouage */
const defaultLink = (gallery) => `/realisations?realisation=${gallery.slug}`;

// Aperçu du timelapse au survol : seulement avec une souris (pas de survol sur écran tactile)
// et si le réglage "réduire les animations" n'est pas activé
const PREVIEW_QUERY = '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)';

/**
 * Mosaïque de réalisations : la première est mise en avant (2 × 2 cases).
 * Survol : la légende apparaît et l'image est entourée de cyan.
 * Timelapse (backoffice) : badge "Timelapse" sur la vignette ; à la souris, la vidéo
 * se lance en aperçu au survol, à la place de la photo.
 * getLink : adresse ouverte au clic (la page Réalisations y garde son filtre de catégorie).
 */
const GalleryGrid = ({ galleries, getLink = defaultLink }) => {
  const canPreview = useMediaQuery(PREVIEW_QUERY);
  // Une seule vidéo chargée à la fois : celle de la vignette survolée
  const [hoveredId, setHoveredId] = useState(null);

  return (
    <div className="gallery-grid">
      {galleries.map((gallery, index) => {
        const showPreview = canPreview && gallery.timelapseUrl && hoveredId === gallery.id;

        return (
          <Link
            key={gallery.id}
            to={getLink(gallery)}
            // La page ne remonte pas en haut à l'ouverture de la visionneuse
            preventScrollReset
            className={`gallery-grid__item ${index === 0 ? 'gallery-grid__item--featured' : ''}`}
            onMouseEnter={gallery.timelapseUrl ? () => setHoveredId(gallery.id) : undefined}
            onMouseLeave={gallery.timelapseUrl ? () => setHoveredId(null) : undefined}
          >
            <img src={assetUrl(gallery.mainPictureUrl)} alt={gallery.name} loading="lazy" />

            {/* Aperçu : la vidéo n'est ajoutée (et donc téléchargée) qu'au survol */}
            {showPreview && (
              <video
                className="gallery-grid__preview"
                src={assetUrl(gallery.timelapseUrl)}
                autoPlay
                muted
                loop
                playsInline
                aria-hidden="true"
              />
            )}

            {/* Coup de cœur de l'artiste (case à cocher du backoffice) : annotation au feutre */}
            {gallery.isFavorite && (
              <Scribble decorative={false} className="gallery-grid__favorite">coup de cœur ♥</Scribble>
            )}

            {gallery.timelapseUrl && (
              <span className="gallery-grid__badge">
                <FaPlay size={10} aria-hidden="true" /> Timelapse
              </span>
            )}

            <span className="gallery-grid__caption">
              {gallery.category && <span className="text-cyan">{gallery.category.name} — </span>}
              {gallery.name}
            </span>
          </Link>
        );
      })}
    </div>
  );
};

export default GalleryGrid;
