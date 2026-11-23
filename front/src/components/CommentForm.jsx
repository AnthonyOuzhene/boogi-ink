import { useState } from 'react';
import { toast } from 'react-toastify';
import api, { getViolations } from '../api/client';

const EMPTY_FORM = { authorName: '', title: '', projectName: '', message: '', website: '' };

/**
 * Formulaire PUBLIC du livre d'or : pas de compte, chacun peut laisser un avis.
 * Chaque avis arrive "en attente" et n'apparaît qu'après validation dans le backoffice.
 */
const CommentForm = () => {
  // Un seul état pour tout le formulaire (plutôt qu'un useState par champ)
  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);
  const [sent, setSent] = useState(false); // message de remerciement après l'envoi

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setSending(true);
    try {
      // POST /api/comments (GuestbookController) : l'avis est créé "en attente de validation"
      await api.post('/comments', {
        authorName: form.authorName,
        title: form.title,
        message: form.message,
        projectName: form.projectName || null,
        website: form.website, // champ piège anti-robots, vide pour un humain
      });
      setForm(EMPTY_FORM);
      setSent(true);
    } catch (error) {
      setErrors(getViolations(error));
      toast.error("L'envoi a échoué, vérifie le formulaire.");
    } finally {
      setSending(false);
    }
  };

  if (sent) {
    return (
      <div className="neon-card cta-card">
        <h2 className="section-title section-title--small">Merci <span className="text-cyan">pour ton avis !</span></h2>
        <p className="muted">Il apparaîtra dans le livre d'or dès que le salon l'aura validé.</p>
        <div className="cta-card__actions">
          <button type="button" className="btn btn--ghost" onClick={() => setSent(false)}>Laisser un autre avis</button>
        </div>
      </div>
    );
  }

  return (
    <div className="neon-card">
      <h2 className="section-title form-title">Partage ton <span className="text-cyan">expérience</span></h2>
      <p className="muted form-intro">Pas besoin de compte. Ton avis sera publié après validation par le salon.</p>

      <form className="form" onSubmit={handleSubmit}>
        <div className="form-row">
          <div className="field">
            <label htmlFor="authorName">Ton prénom ou un pseudo</label>
            <input id="authorName" name="authorName" type="text" required maxLength={80} autoComplete="given-name"
              value={form.authorName} onChange={handleChange} />
            {errors.authorName && <p className="field__error">{errors.authorName}</p>}
          </div>
          <div className="field">
            <label htmlFor="projectName">Ton tatouage (facultatif)</label>
            <input id="projectName" name="projectName" type="text" placeholder="Ex. : rose sur l'avant-bras" maxLength={150}
              value={form.projectName} onChange={handleChange} />
            {errors.projectName && <p className="field__error">{errors.projectName}</p>}
          </div>
        </div>

        <div className="field">
          <label htmlFor="title">Titre</label>
          <input id="title" name="title" type="text" placeholder="En quelques mots" required maxLength={150}
            value={form.title} onChange={handleChange} />
          {errors.title && <p className="field__error">{errors.title}</p>}
        </div>

        <div className="field">
          <label htmlFor="message">Ton avis</label>
          <textarea id="message" name="message" placeholder="Raconte-moi ton expérience" required maxLength={5000}
            value={form.message} onChange={handleChange} />
          {errors.message && <p className="field__error">{errors.message}</p>}
        </div>

        {/* Anti-spam ("pot de miel") : champ invisible pour les visiteurs, hors du parcours clavier
            et ignoré par les lecteurs d'écran ; seuls les robots le remplissent */}
        <div className="visually-hidden" aria-hidden="true">
          <label htmlFor="website">Ne pas remplir</label>
          <input id="website" name="website" type="text" tabIndex={-1} autoComplete="off"
            value={form.website} onChange={handleChange} />
        </div>

        <div>
          <button type="submit" className="btn btn--primary" disabled={sending}>
            {sending ? 'Envoi…' : 'Envoyer mon avis'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CommentForm;
