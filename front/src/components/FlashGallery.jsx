import { useState } from 'react';
import { Link } from 'react-router';
import { assetUrl } from '../api/client';
import { formatPrice } from '../utils/format';
import './FlashGallery.scss';

/**
 * Un flash, présenté comme sur une planche de flashs de salon : le dessin posé à même
 * le papier, un numéro, le nom, et le prix "écrit au feutre".
 * Réservé : tampon rouge "Réservé" de travers sur le dessin.
 */
export const FlashCard = ({ flash, number }) => {
  const details = [flash.size, flash.placement].filter(Boolean).join(' · ');

  return (
    <article className={flash.isAvailable ? 'flash-card' : 'flash-card flash-card--reserved'}>
      <div className="flash-card__img">
        {flash.pictureUrl && <img src={assetUrl(flash.pictureUrl)} alt={`Dessin du flash « ${flash.name} »`} loading="lazy" />}
        {!flash.isAvailable && <span className="flash-card__stamp" aria-hidden="true">Réservé</span>}
      </div>
      <div className="flash-card__body">
        <h3 className="flash-card__name">
          <span className="flash-card__number">N°{String(number).padStart(2, '0')}</span>
          {flash.name}
        </h3>
        {/* Le tampon est décoratif : le statut est donné en texte aux lecteurs d'écran */}
        <span className="visually-hidden">{flash.isAvailable ? 'Disponible' : 'Réservé'}</span>
        {details && <p className="flash-card__details muted">{details}</p>}
        {/* Prix fixe, toujours renseigné (obligatoire dans le backoffice) : jamais "Sur devis" */}
        <p className="flash-card__price">{formatPrice(flash.price)}</p>
      </div>
    </article>
  );
};

/**
 * La planche de flashs (aperçu de l'accueil, page Flashs…).
 * numberOf : numéro d'un flash ; par défaut sa place dans la liste affichée.
 */
export const FlashGrid = ({ flashes, numberOf = (flash, index) => index + 1 }) => (
  <div className="flash-grid">
    {flashes.map((flash, index) => <FlashCard key={flash.id} flash={flash} number={numberOf(flash, index)} />)}
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
        // Numéro = place dans la liste complète : un flash garde son numéro quel que soit le filtre
        ? <FlashGrid flashes={visible} numberOf={(flash) => flashes.indexOf(flash) + 1} />
        : <p className="muted">Aucun flash {filter === 'reserved' ? 'réservé' : 'disponible'} pour le moment.</p>}

      <div className="flash-cta">
        <p>Un flash te plaît ? <span className="muted">Réserve-le en nous indiquant son nom.</span></p>
        <Link to="/contact" className="btn btn--primary">Réserver un flash</Link>
      </div>
    </>
  );
};

export default FlashGallery;
