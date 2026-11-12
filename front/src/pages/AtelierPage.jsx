import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import PageHero from '../components/PageHero';
import ServiceList from '../components/ServiceList';
import ArtistSection from '../components/ArtistSection';
import FaqSection from '../components/FaqSection';
import './pages.scss';

const AtelierPage = () => {
  const { data: services } = useApi('/services');

  return (
    <>
      <PageHero
        eyebrow="Le salon"
        title={<>L'ate<span className="text-cyan">lier</span></>}
        lead="Chaque projet commence par un échange : nous dessinons avec vous un tatouage qui vous ressemble, du premier croquis jusqu'à la séance."
      />

      <div className="section section--tight">
        <ArtistSection />
      </div>

      <section className="container section split">
        <div className="split__intro">
          <h2 className="section-title">Presta<span className="text-cyan">tions</span></h2>
          <p className="muted">Tarifs indicatifs. Chaque projet commence par un échange pour établir un prix précis.</p>
          <Link to="/contact" className="btn btn--primary">Demander un devis</Link>
        </div>
        <div className="split__list">
          {services && <ServiceList services={services} withDetails />}
        </div>
      </section>

      <FaqSection />
    </>
  );
};

export default AtelierPage;
