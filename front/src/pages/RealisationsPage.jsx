import { useSearchParams } from 'react-router';
import useApi from '../hooks/useApi';
import PageHero from '../components/PageHero';
import GalleryGrid from '../components/GalleryGrid';
import './pages.scss';

const RealisationsPage = () => {
  // Le filtre est stocké dans l'URL (?category=2) : la page filtrée peut être partagée,
  // et le bouton "précédent" du navigateur revient au filtre précédent
  const [searchParams, setSearchParams] = useSearchParams();
  const categoryId = searchParams.get('category');

  const { data: categories } = useApi('/categories');
  const { data: galleries, loading } = useApi(categoryId ? `/galleries?category=${categoryId}` : '/galleries');

  const selectCategory = (id) => setSearchParams(id ? { category: id } : {});

  return (
    <>
      <PageHero eyebrow="Galerie" title={<>Réali<span className="text-cyan">sations</span></>}>
        {categories?.length > 0 && (
          <div className="filters" role="group" aria-label="Filtrer par style">
            <button type="button" aria-pressed={!categoryId} onClick={() => selectCategory(null)}>Tout</button>
            {categories.map((category) => (
              <button
                type="button"
                key={category.id}
                aria-pressed={categoryId === String(category.id)}
                onClick={() => selectCategory(category.id)}
              >
                {category.name}
              </button>
            ))}
          </div>
        )}
      </PageHero>

      <section className="container section section--tight">
        {!loading && galleries?.length === 0 && <p className="muted">Aucune réalisation dans ce style pour le moment.</p>}
        {galleries?.length > 0 && <GalleryGrid galleries={galleries} />}
      </section>
    </>
  );
};

export default RealisationsPage;
