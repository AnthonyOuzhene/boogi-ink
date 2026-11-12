import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import { toParagraphs } from '../utils/format';
import './FaqSection.scss';

/**
 * Questions fréquentes, gérées dans le backoffice (menu "FAQ").
 * Bandeau pleine largeur sur fond clair : il marque une vraie coupure avec la section précédente.
 * L'API renvoie les questions déjà triées ; rien n'est affiché s'il n'y en a aucune.
 */
const FaqSection = () => {
  const { data: faqs } = useApi('/faqs');
  if (!faqs?.length) return null;

  return (
    // id="faq" : permet d'y accéder directement via un lien "/#faq"
    <section id="faq" className="faq-band" aria-labelledby="faq-title">
      <span className="faq-band__mark" aria-hidden="true">?</span>

      <div className="container faq-band__inner">
        <header className="faq-band__head">
          <p className="eyebrow">Avant votre séance</p>
          <h2 id="faq-title" className="section-title">Questions <span className="text-cyan">fréquentes</span></h2>
        </header>

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

        <p className="faq-band__more muted">
          Une autre question ? <Link to="/contact">Écrivez-nous</Link>
        </p>
      </div>
    </section>
  );
};

export default FaqSection;
