import { useParams } from 'react-router';
import useApi from '@/hooks/useApi';
import usePageTitle from '@/hooks/usePageTitle';
import { formatDate, toBlocks } from '@/utils/format';
import PageHero from '@/components/ui/PageHero';
import NotFoundPage from './NotFoundPage';
import './ContentPage.scss';

// Pages du backoffice (menu "Pages") : /pages/mentions-legales
const ContentPage = () => {
  const { slug } = useParams();
  const { data: page, loading, error } = useApi(`/pages/${slug}`);
  // Hook appelé avant tout return : un hook doit l'être à chaque rendu
  usePageTitle(error?.response?.status === 404 ? 'Page introuvable' : (loading ? null : page?.title));

  if (error?.response?.status === 404) return <NotFoundPage />;
  if (loading) return <p className="container section muted">Chargement…</p>;
  if (error) return <p className="container section muted">Impossible de charger cette page.</p>;

  return (
    <>
      <PageHero eyebrow={`Mise à jour le ${formatDate(page.updatedAt)}`} title={page.title} />

      <div className="band band--flush paper">
        <article className="container content-page">
          {toBlocks(page.content).map((block) => (block.type === 'heading'
            ? <h2 key={block.text}>{block.text}</h2>
            : <p key={block.text}>{block.text}</p>))}
        </article>
      </div>
    </>
  );
};

export default ContentPage;
