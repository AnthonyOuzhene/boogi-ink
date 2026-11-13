import useApi from '../hooks/useApi';
import { formatDate } from '../utils/format';
import PageHero from '../components/PageHero';
import CommentForm from '../components/CommentForm';
import './pages.scss';

// Couleurs des punaises, en alternance (voir .pin dans global.scss)
const PIN_COLORS = ['', 'pin--blue', 'pin--yellow'];

const VisitorBookPage = () => {
  // L'API ne renvoie que les avis validés dans le backoffice, du plus récent au plus ancien
  const { data: comments, loading, error } = useApi('/comments');

  return (
    <>
      <PageHero
        eyebrow="Ils m'ont fait confiance"
        title={<>Livre <span className="text-cyan">d'or</span></>}
      />

      {/* Les avis sur papier ; le formulaire reste sur fond sombre en dessous */}
      <section className="band band--flush paper">
        <div className="container">
          {loading && <p className="muted">Chargement…</p>}
          {error && <p className="muted">Impossible de charger les avis.</p>}
          {comments?.length === 0 && <p className="muted">Aucun avis pour le moment. Sois le premier !</p>}

          <div className="comments">
            {comments?.map((comment, index) => (
              <article key={comment.id} className="comment">
                {/* Punaise décorative, couleur en alternance */}
                <span className={`pin ${PIN_COLORS[index % PIN_COLORS.length]}`} aria-hidden="true" />
                <span className="comment__mark" aria-hidden="true">“</span>
                <h2 className="comment__title">{comment.title}</h2>
                <p className="comment__message">{comment.message}</p>
                <p className="comment__meta">
                  <span>{comment.author.name}</span>
                  {comment.projectName && <span className="text-cyan">{comment.projectName}</span>}
                  <span className="muted">{formatDate(comment.createdAt)}</span>
                </p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="container section">
        <CommentForm />
      </section>
    </>
  );
};

export default VisitorBookPage;
