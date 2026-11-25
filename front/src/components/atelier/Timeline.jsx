import './Timeline.scss';

/**
 * Frise de dates (backoffice : "Frises").
 * - direction="vertical" : histoire du salon, avec des points lumineux sur une ligne cyan
 * - direction="horizontal" : parcours de l'artiste, en colonnes sous sa biographie
 * Le dernier élément est mis en valeur (c'est en général "Aujourd'hui").
 */
const Timeline = ({ milestones, direction = 'vertical' }) => (
  <ol className={`timeline timeline--${direction}`}>
    {milestones.map((milestone) => (
      <li key={milestone.id} className="timeline__item">
        <span className="timeline__year">{milestone.year}</span>
        <strong className="timeline__title">{milestone.title}</strong>
        {milestone.description && <p className="timeline__text muted">{milestone.description}</p>}
      </li>
    ))}
  </ol>
);

export default Timeline;
