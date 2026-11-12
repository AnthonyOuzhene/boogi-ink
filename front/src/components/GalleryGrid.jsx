import { Link } from 'react-router';
import { assetUrl } from '../api/client';
import './GalleryGrid.scss';

/**
 * Mosaïque de réalisations : la première est mise en avant (2 × 2 cases).
 * Survol : la légende apparaît et l'image est entourée de cyan.
 */
const GalleryGrid = ({ galleries }) => (
  <div className="gallery-grid">
    {galleries.map((gallery, index) => (
      <Link
        key={gallery.id}
        to={`/realisations/${gallery.id}`}
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
