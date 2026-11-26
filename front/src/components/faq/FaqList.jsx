import { toParagraphs } from '@/utils/format';
import './FaqList.scss';

/** Utilisé par la page /faq et par l'aperçu de l'accueil (FaqSection) */
const FaqList = ({ faqs }) => (
  <div className="faq">
    {faqs.map((faq, index) => (
      // <details>/<summary> : accordéon natif, accessible au clavier sans JavaScript
      <details key={faq.id} className="faq__item">
        <summary>
          <span className="faq__number" aria-hidden="true">{String(index + 1).padStart(2, '0')}</span>
          <span className="faq__question">{faq.question}</span>
          <span className="faq__icon" aria-hidden="true" />
        </summary>
        <div className="faq__answer">
          {toParagraphs(faq.answer).map((paragraph) => <p key={paragraph}>{paragraph}</p>)}
        </div>
      </details>
    ))}
  </div>
);

export default FaqList;
