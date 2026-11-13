import useApi from '../hooks/useApi';
import PageHero from '../components/PageHero';
import FlashGallery from '../components/FlashGallery';
import './pages.scss';

/** Tous les flashs (backoffice : "Flashs"), disponibles d'abord */
const FlashsPage = () => {
  const { data: flashes, loading } = useApi('/flashes');

  return (
    <>
      <PageHero
        eyebrow="Prêts à tatouer"
        title={<>Les <span className="glow">flashs</span></>}
        lead="Des motifs dessinés à l'avance par l'artiste. Chaque flash n'est tatoué qu'une seule fois : une fois réservé, il est à vous."
      />
      {/* Sur papier : les dessins (fond blanc) s'y fondent */}
      <section className="band band--flush paper">
        <div className="container">
          {flashes?.length > 0 && <FlashGallery flashes={flashes} />}
          {!loading && !flashes?.length && <p className="muted">Pas de flash disponible pour le moment. Revenez bientôt !</p>}
        </div>
      </section>
    </>
  );
};

export default FlashsPage;
