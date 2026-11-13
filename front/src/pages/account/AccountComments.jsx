import { useState } from 'react';
import { Link } from 'react-router';
import useApi from '../../hooks/useApi';
import { formatDate } from '../../utils/format';
import CommentEditForm from '../../components/CommentEditForm';
import '../pages.scss';

/** /profil/avis : les avis de l'utilisateur connecté (publiés ou en attente), modifiables */
const AccountComments = () => {
  // GET /api/me/comments : uniquement MES avis (l'API déduit l'auteur du jeton JWT)
  const { data: comments, loading, error, reload } = useApi('/me/comments');
  // Avis en cours de modification (un seul à la fois) ; null = aucun
  const [editingId, setEditingId] = useState(null);

  const pendingCount = comments?.filter((comment) => !comment.isPublished).length ?? 0;

  const handleSaved = () => {
    setEditingId(null);
    reload(); // recharge la liste : statut "en attente" + date de modification à jour
  };

  return (
    <>
      <h2 className="account-section-title">Mes avis</h2>

      {loading && <p className="muted">Chargement…</p>}
      {error && <p className="muted">Impossible de charger vos avis.</p>}

      {comments?.length === 0 && (
        <div className="account-empty">
          <p className="muted">Vous n'avez pas encore laissé d'avis dans le livre d'or.</p>
          <Link to="/livre-d-or" className="btn btn--primary">Laisser un avis</Link>
        </div>
      )}

      {pendingCount > 0 && (
        <p className="muted">
          {pendingCount === 1 ? 'Un avis est' : `${pendingCount} avis sont`} en attente de validation :
          {' '}{pendingCount === 1 ? 'il apparaîtra' : 'ils apparaîtront'} dans le livre d'or une fois validé{pendingCount > 1 && 's'} par le salon.
        </p>
      )}

      {comments?.length > 0 && (
        <div className="my-comments">
          {comments.map((comment) => (
            <article key={comment.id} className={`comment ${editingId === comment.id ? 'comment--editing' : ''}`}>
              <div className="comment__top">
                <span className={`status-badge ${comment.isPublished ? 'status-badge--published' : 'status-badge--pending'}`}>
                  {comment.isPublished ? 'Publié' : 'En attente de validation'}
                </span>
                {editingId !== comment.id && (
                  <button
                    type="button"
                    className="btn btn--ghost comment__edit-btn"
                    onClick={() => setEditingId(comment.id)}
                    aria-label={`Modifier l'avis « ${comment.title} »`}
                  >
                    Modifier
                  </button>
                )}
              </div>

              {editingId === comment.id ? (
                <CommentEditForm comment={comment} onSaved={handleSaved} onCancel={() => setEditingId(null)} />
              ) : (
                <>
                  <h3 className="comment__title">{comment.title}</h3>
                  <p className="comment__message">{comment.message}</p>
                  <p className="comment__meta">
                    {comment.projectName && <span className="text-cyan">{comment.projectName}</span>}
                    <span className="muted">Déposé le {formatDate(comment.createdAt)}</span>
                    {comment.updatedAt && <span className="muted">Modifié le {formatDate(comment.updatedAt)}</span>}
                  </p>
                </>
              )}
            </article>
          ))}
        </div>
      )}
    </>
  );
};

export default AccountComments;
