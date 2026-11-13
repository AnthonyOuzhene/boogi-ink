import { useState } from 'react';
import { Link } from 'react-router';
import { assetUrl } from '../api/client';
import { formatPrice } from '../utils/format';
import './FlashGallery.scss';

/** Carte d'un flash : dessin, badge Disponible / Réservé, nom, taille, prix */
export const FlashCard = ({ flash }) => {
  const details = [flash.size, flash.placement].filter(Boolean).join(' · ');

  return (
    <article className={flash.isAvailable ? 'flash-card' : 'flash-card flash-card--reserved'}>
      <div className="flash-card__img">
        {flash.pictureUrl && <img src={assetUrl(flash.pictureUrl)} alt={`Dessin du flash « ${flash.name} »`} loading="lazy" />}
      </div>
      <div className="flash-card__body">
        <span className={flash.isAvailable ? 'badge badge--ok' : 'badge badge--reserved'}>
          {flash.isAvailable ? 'Disponible' : 'Réservé'}
        </span>
        <h3 className="flash-card__name">{flash.name}</h3>
        {details && <p className="flash-card__details muted">{details}</p>}
        <p className="flash-card__price">{formatPrice(flash.price)}</p>
      </div>
    </article>
  );
};

/** Grille simple, sans filtre (aperçu de l'accueil) */
export const FlashGrid = ({ flashes }) => (
  <div className="flash-grid">
    {flashes.map((flash) => <FlashCard key={flash.id} flash={flash} />)}
  </div>
);

const FILTERS = [
  { key: 'all', label: 'Tous' },
  { key: 'available', label: 'Disponibles' },
  { key: 'reserved', label: 'Réservés' },
];

/**
 * Tous les flashs, avec un filtre Tous / Disponibles / Réservés.
 * Le filtre se fait dans le navigateur : la liste complète est déjà chargée.
 */
const FlashGallery = ({ flashes }) => {
  const [filter, setFilter] = useState('all');

  const visible = flashes.filter((flash) =>
    filter === 'all' || (filter === 'available') === flash.isAvailable);

  return (
    <>
      <div className="filters flash-filters" role="group" aria-label="Filtrer les flashs">
        {FILTERS.map(({ key, label }) => (
          <button key={key} type="button" aria-pressed={filter === key} onClick={() => setFilter(key)}>
            {label}
          </button>
        ))}
      </div>

      {visible.length > 0
        ? <FlashGrid flashes={visible} />
        : <p className="muted">Aucun flash {filter === 'reserved' ? 'réservé' : 'disponible'} pour le moment.</p>}

      <div className="flash-cta">
        <p>Un flash vous plaît ? <span className="muted">Réservez-le en nous indiquant son nom.</span></p>
        <Link to="/contact" className="btn btn--primary">Réserver un flash</Link>
      </div>
    </>
  );
};

export default FlashGallery;
