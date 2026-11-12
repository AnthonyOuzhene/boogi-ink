import { Link, useParams } from 'react-router';
import useApi from '../hooks/useApi';
import { assetUrl } from '../api/client';
import { formatDate } from '../utils/format';
import PageHero from '../components/PageHero';
import NotFoundPage from './NotFoundPage';
import './pages.scss';

/** Une réalisation : /realisations/12 */
const GalleryDetailPage = () => {
  // useParams lit la partie variable de l'URL déclarée dans la route ("/realisations/:id")
  const { id } = useParams();
  const { data: gallery, loading, error } = useApi(`/galleries/${id}`);

  if (error?.response?.status === 404) return <NotFoundPage />;
  if (loading) return <p className="container section muted">Chargement…</p>;
  if (error) return <p className="container section muted">Impossible de charger cette réalisation.</p>;

  const pictures = [gallery.mainPictureUrl, ...(gallery.pictures ?? []).map((picture) => picture.url)];
  const meta = [gallery.category?.name, gallery.realisedAt && formatDate(gallery.realisedAt)].filter(Boolean).join(' — ');

  return (
    <>
      <PageHero eyebrow={meta || 'Réalisation'} title={gallery.name}>
        <Link to="/realisations" className="link-arrow">← Toutes les réalisations</Link>
      </PageHero>

      <section className="container section section--tight">
        <div className="photo-stack">
          {pictures.map((url, index) => (
            <a key={url} href={assetUrl(url)} target="_blank" rel="noreferrer" className={index === 0 ? 'is-main' : ''}>
              <img src={assetUrl(url)} alt={`${gallery.name} — photo ${index + 1}`} />
            </a>
          ))}
        </div>

        {gallery.video && (
          <p className="detail-actions">
            <a href={gallery.video} target="_blank" rel="noreferrer" className="btn btn--ghost">Voir la vidéo</a>
          </p>
        )}

        <div className="detail-actions">
          <Link to="/contact" className="btn btn--primary">Un projet dans ce style ? Parlons-en</Link>
        </div>
      </section>
    </>
  );
};

export default GalleryDetailPage;
