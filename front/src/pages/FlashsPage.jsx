import useApi from '@/hooks/useApi';
import usePageTitle from '@/hooks/usePageTitle';
import PageHero from '@/components/ui/PageHero';
import FlashGallery from '@/components/flash/FlashGallery';

// L'API renvoie les flashs disponibles en premier
const FlashsPage = () => {
  usePageTitle('Flashs');
  const { data: flashes, loading } = useApi('/flashes');

  return (
    <>
      <PageHero
        eyebrow="Prêts à tatouer"
        title={<>Les <span className="glow">flashs</span></>}
        lead="Des motifs dessinés à l'avance par l'artiste. Chaque flash n'est tatoué qu'une seule fois : une fois réservé, il est à toi."
      />
      {/* Fond papier : les dessins (fond blanc) s'y fondent */}
      <section className="band band--flush paper">
        <div className="container">
          {flashes?.length > 0 && <FlashGallery flashes={flashes} />}
          {!loading && !flashes?.length && <p className="muted">Pas de flash disponible pour le moment. Reviens bientôt !</p>}
        </div>
      </section>
    </>
  );
};

export default FlashsPage;
