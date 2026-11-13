import './Scribble.scss';

/**
 * Annotation "écrite au feutre", comme si l'artiste avait annoté son site :
 * texte manuscrit légèrement de travers + flèche dessinée à la main (facultative).
 * À utiliser avec parcimonie (3 ou 4 sur tout le site), sinon l'effet s'use.
 *
 * - arrow : direction de la flèche ("left", "right", "down", "down-left") ou rien ;
 *   à gauche ("left", "down-left") elle est dessinée avant le texte, sinon après.
 * - decorative : true (défaut) = ignorée par les lecteurs d'écran ; false si le texte
 *   apporte une information (ex. "coup de cœur").
 */
const Arrow = () => (
  <svg className="scribble__arrow" viewBox="0 0 64 40" aria-hidden="true">
    {/* Trait courbe un peu irrégulier, puis la pointe en deux coups de feutre */}
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
