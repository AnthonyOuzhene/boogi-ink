import { assetUrl } from '../../api/client';
import './atelier.scss';

/** "Le lieu en images" : la première photo en grand, les suivantes autour (backoffice : "Photos du salon") */
const StudioGallery = ({ photos }) => (
  <ul className="studio-gallery">
    {photos.map((photo, index) => (
      <li key={photo.id} className={index === 0 ? 'studio-gallery__item is-main' : 'studio-gallery__item'}>
        <img src={assetUrl(photo.pictureUrl)} alt={photo.caption ?? ''} loading="lazy" />
        {photo.caption && <span className="studio-gallery__caption">{photo.caption}</span>}
      </li>
    ))}
  </ul>
);

export default StudioGallery;
