import './HygieneCards.scss';

// Pictogrammes au trait : la clé correspond au champ "icon" choisi dans le backoffice
// (liste HygieneCommitment::ICONS côté Symfony)
const ICON_PATHS = {
  shield: ['M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z', 'M9 12l2 2 4-4'],
  ink: ['M9 3h6', 'M10 3v5l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3'],
  training: ['M3 7l9-4 9 4-9 4-9-4z', 'M7 9v5c0 1.5 2.5 3 5 3s5-1.5 5-3V9'],
  clean: ['M3 12h4l3 8 4-16 3 8h4'],
};

const HygieneIcon = ({ name }) => (
  <span className="hygiene-card__icon" aria-hidden="true">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      {(ICON_PATHS[name] ?? ICON_PATHS.shield).map((d) => <path key={d} d={d} />)}
    </svg>
  </span>
);

/** Engagements d'hygiène et de sécurité (backoffice : "Hygiène & sécurité") */
const HygieneCards = ({ commitments }) => (
  <ul className="hygiene-cards">
    {commitments.map((commitment) => (
      <li key={commitment.id} className="hygiene-card">
        <HygieneIcon name={commitment.icon} />
        <strong className="hygiene-card__title">{commitment.title}</strong>
        <p className="muted">{commitment.description}</p>
      </li>
    ))}
  </ul>
);

export default HygieneCards;
