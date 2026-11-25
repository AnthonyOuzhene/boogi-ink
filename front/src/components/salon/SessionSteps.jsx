import './SessionSteps.scss';

/** 1 → "01" */
const stepNumber = (index) => String(index + 1).padStart(2, '0');

/**
 * Déroulé d'une séance (backoffice : "Déroulé d'une séance").
 * - variant="preview" (accueil) : cartes côte à côte avec le titre et le résumé
 * - variant="full" (L'atelier) : liste numérotée avec la description complète
 */
const SessionSteps = ({ steps, variant = 'full' }) => {
  if (variant === 'preview') {
    return (
      <ol className="steps-preview">
        {steps.map((step, index) => (
          <li key={step.id} className="steps-preview__item">
            <span className="step-number">{stepNumber(index)}</span>
            <strong className="steps-preview__title">{step.title}</strong>
            {step.summary && <span className="muted">{step.summary}</span>}
          </li>
        ))}
      </ol>
    );
  }

  return (
    <ol className="steps">
      {steps.map((step, index) => (
        <li key={step.id} className="steps__item">
          <span className="step-number step-number--large">{stepNumber(index)}</span>
          <div className="steps__text">
            <strong className="steps__title">{step.title}</strong>
            <p className="muted">{step.description}</p>
          </div>
        </li>
      ))}
    </ol>
  );
};

export default SessionSteps;
