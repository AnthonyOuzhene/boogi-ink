import { Link } from 'react-router';
import { assetUrl } from '../api/client';
import './GalleryGrid.scss';

/** Lien par défaut (accueil) : la page Réalisations, visionneuse ouverte sur ce tatouage */
const defaultLink = (gallery) => `/realisations?realisation=${gallery.id}`;

/**
 * Mosaïque de réalisations : la première est mise en avant (2 × 2 cases).
 * Survol : la légende apparaît et l'image est entourée de cyan.
 * getLink : adresse ouverte au clic (la page Réalisations y garde son filtre de catégorie).
 */
const GalleryGrid = ({ galleries, getLink = defaultLink }) => (
  <div className="gallery-grid">
    {galleries.map((gallery, index) => (
      <Link
        key={gallery.id}
        to={getLink(gallery)}
        // La page ne remonte pas en haut à l'ouverture de la visionneuse
        preventScrollReset
        className={`gallery-grid__item ${index === 0 ? 'gallery-grid__item--featured' : ''}`}
      >
        <img src={assetUrl(gallery.mainPictureUrl)} alt={gallery.name} loading="lazy" />
        <span className="gallery-grid__caption">
          {gallery.category && <span className="text-cyan">{gallery.category.name} — </span>}
          {gallery.name}
        </span>
      </Link>
    ))}
  </div>
);

export default GalleryGrid;
