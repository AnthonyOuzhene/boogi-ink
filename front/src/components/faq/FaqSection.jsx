import { Link } from 'react-router';
import useApi from '@/hooks/useApi';
import FaqList from './FaqList';
import './FaqSection.scss';

const PREVIEW_COUNT = 4;

/** Aperçu de la FAQ sur l'accueil */
const FaqSection = () => {
  const { data: faqs } = useApi('/faqs');
  if (!faqs?.length) return null;

  return (
    <section className="faq-band" aria-labelledby="faq-title">
      <span className="faq-band__mark" aria-hidden="true">?</span>

      <div className="container faq-band__inner">
        <header className="faq-band__head">
          <p className="eyebrow">Avant ta séance</p>
          <h2 id="faq-title" className="section-title section-title--small">Questions <span className="text-cyan">fréquentes</span></h2>
        </header>

        <FaqList faqs={faqs.slice(0, PREVIEW_COUNT)} />

        <div className="faq-band__more">
          <Link to="/faq" className="btn btn--ghost">
            {faqs.length > PREVIEW_COUNT ? `Voir les ${faqs.length} questions` : 'Toutes les questions'}
          </Link>
          <span className="muted">Une autre question ? <Link to="/contact">Écris-moi</Link></span>
        </div>
      </div>
    </section>
  );
};

export default FaqSection;
