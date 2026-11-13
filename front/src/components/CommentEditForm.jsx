import { useState } from 'react';
import { toast } from 'react-toastify';
import api, { getViolations } from '../api/client';

/** "2026-10-15T00:00:00+02:00" → "2026-10-15" (format attendu par <input type="date">) */
const toDateInput = (isoDate) => (isoDate ? isoDate.slice(0, 10) : '');

/**
 * Formulaire de modification d'un avis, affiché à la place de la carte.
 * PATCH /api/me/comments/{id} (AccountController) : seuls titre, message, projet et date
 * sont modifiables ; l'avis repasse ensuite en attente de validation (côté serveur).
 */
const CommentEditForm = ({ comment, onSaved, onCancel }) => {
  const [form, setForm] = useState({
    title: comment.title,
    projectName: comment.projectName ?? '',
    realisedAt: toDateInput(comment.realisedAt),
    message: comment.message,
  });
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });
  const fieldId = (name) => `comment-${comment.id}-${name}`; // ids uniques si plusieurs formulaires

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setSending(true);
    try {
      // PATCH /api/me/comments/{id} (AccountController) : on envoie tout le formulaire
      await api.patch(`/me/comments/${comment.id}`, {
        title: form.title,
        message: form.message,
        projectName: form.projectName || null,
        realisedAt: form.realisedAt || null,
      });
      toast.success('Avis modifié. Il sera de nouveau publié après validation par le salon.');
      onSaved();
    } catch (error) {
      setErrors(getViolations(error));
      toast.error("La modification a échoué, vérifie le formulaire.");
    } finally {
      setSending(false);
    }
  };

  return (
    <form className="form comment-edit" onSubmit={handleSubmit}>
      <div className="field">
        <label htmlFor={fieldId('title')}>Titre</label>
        <input id={fieldId('title')} name="title" type="text" required maxLength={150}
          value={form.title} onChange={handleChange} />
        {errors.title && <p className="field__error">{errors.title}</p>}
      </div>

      <div className="form-row">
        <div className="field">
          <label htmlFor={fieldId('projectName')}>Projet réalisé (facultatif)</label>
          <input id={fieldId('projectName')} name="projectName" type="text" maxLength={150}
            value={form.projectName} onChange={handleChange} />
          {errors.projectName && <p className="field__error">{errors.projectName}</p>}
        </div>
        <div className="field">
          <label htmlFor={fieldId('realisedAt')}>Date de passage (facultatif)</label>
          <input id={fieldId('realisedAt')} name="realisedAt" type="date"
            value={form.realisedAt} onChange={handleChange} />
        </div>
      </div>

      <div className="field">
        <label htmlFor={fieldId('message')}>Ton avis</label>
        <textarea id={fieldId('message')} name="message" required maxLength={5000}
          value={form.message} onChange={handleChange} />
        {errors.message && <p className="field__error">{errors.message}</p>}
      </div>

      <p className="comment-edit__warning">
        Après modification, ton avis repassera en attente de validation et disparaîtra
        du livre d'or jusqu'à ce que le salon le valide.
      </p>

      <div className="comment-edit__actions">
        <button type="submit" className="btn btn--primary" disabled={sending}>
          {sending ? 'Enregistrement…' : 'Enregistrer'}
        </button>
        <button type="button" className="btn btn--ghost" onClick={onCancel} disabled={sending}>
          Annuler
        </button>
      </div>
    </form>
  );
};

export default CommentEditForm;
