import { useSearchParams } from 'react-router';
import useApi from '@/hooks/useApi';
import usePageTitle from '@/hooks/usePageTitle';
import PageHero from '@/components/ui/PageHero';
import GalleryGrid from '@/components/gallery/GalleryGrid';
import GalleryViewer from '@/components/gallery/GalleryViewer';

const RealisationsPage = () => {
  // État dans l'URL (partageable) : ?category=<slug> filtre, ?realisation=<slug> ouvre la visionneuse
  const [searchParams, setSearchParams] = useSearchParams();
  const categorySlug = searchParams.get('category');
  const openedSlug = searchParams.get('realisation');

  const { data: allCategories } = useApi('/categories');
  const categories = allCategories?.filter((category) => category.galleryCount > 0);
  const { data: galleries, loading } = useApi(
    categorySlug ? `/galleries?category=${encodeURIComponent(categorySlug)}` : '/galleries',
  );

  const selectCategory = (slug) => setSearchParams(slug ? { category: slug } : {});

  // Garde le filtre en cours
  const linkTo = (gallery) => {
    const params = new URLSearchParams(searchParams);
    params.set('realisation', gallery.slug);
    return `?${params}`;
  };

  // La liste contient déjà photos et description : pas d'appel en plus.
  // Les anciens liens avec un numéro (?realisation=12) marchent toujours.
  const openedIndex = galleries?.findIndex(
    (gallery) => gallery.slug === openedSlug || String(gallery.id) === openedSlug,
  ) ?? -1;

  usePageTitle(openedIndex >= 0 ? `${galleries[openedIndex].name} — Réalisations` : 'Réalisations');

  // replace : "précédent" ferme la visionneuse au lieu de rejouer chaque réalisation vue
  const openAt = (index) => setSearchParams(linkTo(galleries[index]), { replace: true, preventScrollReset: true });

  const closeViewer = () => {
    const params = new URLSearchParams(searchParams);
    params.delete('realisation');
    setSearchParams(params, { replace: true, preventScrollReset: true });
  };

  return (
    <>
      <PageHero eyebrow="Galerie" title={<>Réali<span className="text-cyan">sations</span></>}>
        {categories?.length > 0 && (
          <div className="filters" role="group" aria-label="Filtrer par style">
            <button type="button" aria-pressed={!categorySlug} onClick={() => selectCategory(null)}>Tout</button>
            {categories.map((category) => (
              <button
                type="button"
                key={category.id}
                aria-pressed={categorySlug === category.slug}
                onClick={() => selectCategory(category.slug)}
              >
                {category.name}
              </button>
            ))}
          </div>
        )}
      </PageHero>

      <section className="container section section--tight">
        {!loading && galleries?.length === 0 && <p className="muted">Aucune réalisation dans ce style pour le moment.</p>}
        {galleries?.length > 0 && <GalleryGrid galleries={galleries} getLink={linkTo} />}
      </section>

      {openedIndex >= 0 && (
        <GalleryViewer
          gallery={galleries[openedIndex]}
          position={openedIndex + 1}
          total={galleries.length}
          onPrevious={() => openAt(openedIndex - 1)}
          onNext={() => openAt(openedIndex + 1)}
          onClose={closeViewer}
        />
      )}
    </>
  );
};

export default RealisationsPage;
