import { useEffect, useState } from 'react';
import { Link } from 'react-router';
import { FaArrowLeft, FaArrowRight } from 'react-icons/fa';
import useApi from '../hooks/useApi';
import { formatDate } from '../utils/format';
import './GuestbookCarousel.scss';

const AUTOPLAY_DELAY = 5500; // ms entre deux avis : le temps de lire, sans traîner
const POSITIONS = [-2, -1, 0, 1, 2]; // 0 = avis courant, ±1 = voisins visibles, ±2 = hors champ
const PIN_COLORS = ['', 'pin--blue', 'pin--yellow']; // punaises en alternance (voir .pin dans global.scss)

// Respecte le réglage système "réduire les animations" : pas de défilement automatique
const prefersReducedMotion = () =>
  typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Reste positif de la division (JavaScript renvoie -1 % 3 === -1) */
const mod = (value, length) => ((value % length) + length) % length;

/**
 * Carrousel du livre d'or : l'avis courant au centre, le précédent et le suivant
 * dépassent de chaque côté. Défilement automatique, en pause au survol ou au clavier.
 * className : classe ajoutée à la section (ex. "paper" pour le fond clair).
 */
const GuestbookCarousel = ({ className = '' }) => {
  const { data: comments } = useApi('/comments');

  // "current" n'est jamais ramené entre 0 et n-1 : il sert aussi de clé stable aux cartes,
  // ce qui permet à React de les faire GLISSER d'une position à l'autre au lieu de les recréer
  const [current, setCurrent] = useState(0);

  // Deux raisons de mettre en pause, chacune avec son propre état :
  const [hovered, setHovered] = useState(false);             // souris sur l'avis central (on le lit)
  const [keyboardFocus, setKeyboardFocus] = useState(false); // navigation au clavier dans le carrousel

  const count = comments?.length ?? 0;
  const autoplay = count > 1 && !hovered && !keyboardFocus && !prefersReducedMotion();

  // Défilement automatique : relancé après chaque changement d'avis
  useEffect(() => {
    if (!autoplay) return undefined;
    const timer = setTimeout(() => setCurrent((index) => index + 1), AUTOPLAY_DELAY);
    return () => clearTimeout(timer);
  }, [autoplay, current]);

  if (count === 0) return null;

  const goTo = (index) => {
    setHovered(false); // l'avis central change : il n'est plus forcément sous la souris
    setCurrent(index);
  };
  const activeIndex = mod(current, count);

  return (
    <section
      className={`guestbook ${className}`}
      aria-roledescription="carrousel"
      aria-labelledby="guestbook-title"
      // Pause seulement pour le focus CLAVIER (:focus-visible) : un clic de souris sur une flèche
      // laisse le focus sur le bouton, et ne doit pas bloquer le défilement
      onFocus={(e) => setKeyboardFocus(e.target.matches(':focus-visible'))}
      // Le focus quitte vraiment le carrousel (et pas juste un bouton pour un autre bouton du carrousel)
      onBlur={(e) => { if (!e.currentTarget.contains(e.relatedTarget)) setKeyboardFocus(false); }}
    >
      <div className="container guestbook__head">
        <div>
          <p className="eyebrow">Ils m'ont fait confiance</p>
          <h2 id="guestbook-title" className="section-title">Livre <span className="text-cyan">d'or</span></h2>
        </div>
        <Link to="/livre-d-or" className="link-arrow">Tous les avis →</Link>
      </div>

      {/* aria-live : les lecteurs d'écran annoncent l'avis courant, sauf pendant le défilement automatique */}
      <div className="guestbook__track" aria-live={autoplay ? 'off' : 'polite'}>
        {(count === 1 ? [0] : POSITIONS).map((position) => {
          const index = current + position;
          const comment = comments[mod(index, count)];
          const isCurrent = position === 0;
          return (
            <article
              key={index}
              className="guestbook__slide"
              data-position={position}
              aria-hidden={!isCurrent}
              aria-roledescription="avis"
              // Clic sur un avis voisin : il passe au centre
              onClick={isCurrent ? undefined : () => goTo(index)}
              // Pause uniquement quand la souris est sur l'avis central (le temps de le lire)
              onMouseEnter={isCurrent ? () => setHovered(true) : undefined}
              onMouseLeave={isCurrent ? () => setHovered(false) : undefined}
            >
              {/* Punaise décorative : l'avis est "épinglé", couleur en alternance */}
              <span className={`pin ${PIN_COLORS[mod(index, count) % PIN_COLORS.length]}`} aria-hidden="true" />
              <span className="guestbook__mark" aria-hidden="true">“</span>
              <h3 className="guestbook__title">{comment.title}</h3>
              <blockquote className="guestbook__message">{comment.message}</blockquote>
              <p className="guestbook__meta">
                <span>{comment.author.name}</span>
                {comment.projectName && <span className="text-cyan">{comment.projectName}</span>}
                <span className="muted">{formatDate(comment.createdAt)}</span>
              </p>
            </article>
          );
        })}
      </div>

      {count > 1 && (
        <div className="container guestbook__controls">
          <button type="button" className="guestbook__arrow" aria-label="Avis précédent" onClick={() => goTo(current - 1)}>
            <FaArrowLeft aria-hidden="true" />
          </button>
          <div className="guestbook__dots">
            {comments.map((comment, index) => (
              <button
                type="button"
                key={comment.id}
                className="guestbook__dot"
                aria-label={`Avis ${index + 1} sur ${count}`}
                aria-current={index === activeIndex}
                // On avance (ou recule) du bon nombre de crans pour garder des clés cohérentes
                onClick={() => goTo(current + (index - activeIndex))}
              />
            ))}
          </div>
          <button type="button" className="guestbook__arrow" aria-label="Avis suivant" onClick={() => goTo(current + 1)}>
            <FaArrowRight aria-hidden="true" />
          </button>
        </div>
      )}
    </section>
  );
};

export default GuestbookCarousel;
