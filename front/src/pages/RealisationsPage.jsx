import { useSearchParams } from 'react-router';
import useApi from '../hooks/useApi';
import usePageTitle from '../hooks/usePageTitle';
import PageHero from '../components/PageHero';
import GalleryGrid from '../components/GalleryGrid';
import GalleryViewer from '../components/GalleryViewer';
import './pages.scss';

const RealisationsPage = () => {
  // Tout l'état de la page est dans l'URL, elle peut donc être partagée telle quelle :
  // - ?category=fine-line : filtre par style (slug de la catégorie, lisible)
  // - ?realisation=manchette-graphique : visionneuse ouverte sur cette réalisation (slug du titre)
  // Le bouton "précédent" du navigateur ferme la visionneuse / revient au filtre précédent.
  const [searchParams, setSearchParams] = useSearchParams();
  const categorySlug = searchParams.get('category');
  const openedSlug = searchParams.get('realisation');

  const { data: allCategories } = useApi('/categories');
  // Seulement les styles qui ont au moins une réalisation : pas de filtre qui mène à une page vide
  const categories = allCategories?.filter((category) => category.galleryCount > 0);
  // L'API filtre directement par slug : GET /api/galleries?category=fine-line
  const { data: galleries, loading } = useApi(
    categorySlug ? `/galleries?category=${encodeURIComponent(categorySlug)}` : '/galleries',
  );

  const selectCategory = (slug) => setSearchParams(slug ? { category: slug } : {});

  // Adresse d'une réalisation dans la visionneuse, en gardant le filtre en cours
  const linkTo = (gallery) => {
    const params = new URLSearchParams(searchParams);
    params.set('realisation', gallery.slug);
    return `?${params}`;
  };

  // La liste renvoyée par l'API contient déjà photos et description : pas de nouvel appel.
  // Les anciens liens partagés avec le numéro (?realisation=12) fonctionnent toujours.
  const openedIndex = galleries?.findIndex(
    (gallery) => gallery.slug === openedSlug || String(gallery.id) === openedSlug,
  ) ?? -1;

  // Visionneuse ouverte : le titre de l'onglet donne le nom du tatouage
  usePageTitle(openedIndex >= 0 ? `${galleries[openedIndex].name} — Réalisations` : 'Réalisations');

  // Passage d'une réalisation à l'autre : "replace" remplace l'entrée de l'historique,
  // pour que "précédent" ferme la visionneuse au lieu de rejouer chaque réalisation vue
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
