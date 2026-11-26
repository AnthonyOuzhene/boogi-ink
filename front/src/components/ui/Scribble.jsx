import './Scribble.scss';

/**
 * Annotation "écrite au feutre" : à utiliser avec parcimonie (3 ou 4 sur tout le site), sinon l'effet s'use.
 * - arrow : "left", "right", "down", "down-left" ou rien
 * - decorative : false si le texte apporte une information (ex. "coup de cœur"),
 *   sinon il est masqué aux lecteurs d'écran
 */
const Arrow = () => (
  <svg className="scribble__arrow" viewBox="0 0 64 40" aria-hidden="true">
    <path d="M4 32 C 16 12, 36 6, 56 16" />
    <path d="M56 16 L 45 15" />
    <path d="M56 16 L 51 6" />
  </svg>
);

const Scribble = ({ children, arrow = null, decorative = true, className = '' }) => {
  const arrowBefore = arrow === 'left' || arrow === 'down-left';

  return (
    <span
      className={`scribble ${arrow ? `scribble--${arrow}` : ''} ${className}`}
      aria-hidden={decorative || undefined}
    >
      {arrow && arrowBefore && <Arrow />}
      <span className="scribble__text">{children}</span>
      {arrow && !arrowBefore && <Arrow />}
    </span>
  );
};

export default Scribble;
