import { useParams } from 'react-router';
import useApi from '../hooks/useApi';
import { formatDate, toBlocks } from '../utils/format';
import PageHero from '../components/PageHero';
import NotFoundPage from './NotFoundPage';
import './pages.scss';

/** Page de contenu gérée dans le backoffice (menu "Pages") : /pages/mentions-legales */
const ContentPage = () => {
  const { slug } = useParams();
  // L'API identifie les pages par leur slug : GET /api/pages/mentions-legales
  const { data: page, loading, error } = useApi(`/pages/${slug}`);

  if (error?.response?.status === 404) return <NotFoundPage />;
  if (loading) return <p className="container section muted">Chargement…</p>;
  if (error) return <p className="container section muted">Impossible de charger cette page.</p>;

  return (
    <>
      <PageHero eyebrow={`Mise à jour le ${formatDate(page.updatedAt)}`} title={page.title} />

      <article className="container section section--tight content-page">
        {toBlocks(page.content).map((block) => (block.type === 'heading'
          ? <h2 key={block.text}>{block.text}</h2>
          : <p key={block.text}>{block.text}</p>))}
      </article>
    </>
  );
};

export default ContentPage;
