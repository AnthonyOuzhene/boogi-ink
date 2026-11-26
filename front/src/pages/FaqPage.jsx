import { Link } from 'react-router';
import useApi from '@/hooks/useApi';
import usePageTitle from '@/hooks/usePageTitle';
import PageHero from '@/components/ui/PageHero';
import FaqList from '@/components/faq/FaqList';
import './FaqPage.scss';

const FaqPage = () => {
  usePageTitle('Questions fréquentes');
  const { data: faqs, loading, error } = useApi('/faqs');

  return (
    <>
      <PageHero
        eyebrow="Avant ta séance"
        title={<>Questions <span className="text-cyan">fréquentes</span></>}
        lead="Tarifs, douleur, cicatrisation, prise de rendez-vous : les réponses aux questions qu'on me pose le plus souvent."
      />

      <section className="band band--flush paper">
        <div className="container faq-page">
          {loading && <p className="muted">Chargement…</p>}
          {error && <p className="muted">Impossible de charger les questions.</p>}
          {faqs?.length === 0 && <p className="muted">Aucune question pour le moment.</p>}
          {faqs?.length > 0 && <FaqList faqs={faqs} />}
        </div>
      </section>

      <section className="container section">
        <div className="neon-card cta-card">
          <h2 className="section-title">Une autre <span className="text-cyan">question ?</span></h2>
          <p className="muted">Écris-moi ou passe au salon : je prends le temps d'en parler avec toi.</p>
          <div className="cta-card__actions">
            <Link to="/contact" className="btn btn--primary">Me contacter</Link>
          </div>
        </div>
      </section>
    </>
  );
};

export default FaqPage;
