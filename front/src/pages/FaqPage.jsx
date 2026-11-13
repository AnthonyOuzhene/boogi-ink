import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import PageHero from '../components/PageHero';
import FaqList from '../components/FaqList';
import './pages.scss';

/** Page /faq : toutes les questions fréquentes, gérées dans le backoffice (menu "FAQ") */
const FaqPage = () => {
  const { data: faqs, loading, error } = useApi('/faqs');

  return (
    <>
      <PageHero
        eyebrow="Avant votre séance"
        title={<>Questions <span className="text-cyan">fréquentes</span></>}
        lead="Tarifs, douleur, cicatrisation, prise de rendez-vous : les réponses aux questions qu'on nous pose le plus souvent."
      />

      {/* Les réponses sur papier : plus confortable à lire */}
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
          <p className="muted">Écrivez-nous ou passez au salon : on prend le temps d'en parler avec vous.</p>
          <div className="cta-card__actions">
            <Link to="/contact" className="btn btn--primary">Nous contacter</Link>
          </div>
        </div>
      </section>
    </>
  );
};

export default FaqPage;
