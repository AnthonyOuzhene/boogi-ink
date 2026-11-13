import { useState } from 'react';
import { Link } from 'react-router';
import { toast } from 'react-toastify';
import api, { getViolations } from '../api/client';
import { useAuth } from '../context/AuthContext';

const EMPTY_FORM = { title: '', projectName: '', realisedAt: '', message: '' };

/** Formulaire du livre d'or (clients connectés) */
const CommentForm = () => {
  const { isConnected, user } = useAuth();

  // Un seul état pour tout le formulaire (plutôt qu'un useState par champ)
  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setSending(true);
    try {
      // POST /api/me/comments (AccountController) : l'auteur n'est pas envoyé,
      // le back le déduit du jeton JWT et crée l'avis "en attente de validation"
      await api.post('/me/comments', {
        title: form.title,
        message: form.message,
        projectName: form.projectName || null,
        realisedAt: form.realisedAt || null,
      });
      toast.success('Merci ! Votre avis sera publié après validation par le salon.');
      setForm(EMPTY_FORM);
    } catch (error) {
      setErrors(getViolations(error));
      toast.error("L'envoi a échoué, vérifiez le formulaire.");
    } finally {
      setSending(false);
    }
  };

  if (!isConnected) {
    return (
      <div className="neon-card cta-card">
        <h2 className="section-title">Partagez votre <span className="text-cyan">expérience</span></h2>
        <p className="muted">Connectez-vous pour laisser un avis sur votre tatouage.</p>
        <div className="cta-card__actions">
          <Link to="/connexion" className="btn btn--primary">Se connecter</Link>
          <Link to="/inscription" className="btn btn--ghost">Créer un compte</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="neon-card">
      <h2 className="section-title form-title">Partagez votre <span className="text-cyan">expérience</span></h2>
      <p className="muted form-intro">Vous publiez en tant que <strong>{user.name}</strong>. Votre avis apparaîtra après validation.</p>

      <form className="form" onSubmit={handleSubmit}>
        <div className="field">
          <label htmlFor="title">Titre</label>
          <input id="title" name="title" type="text" placeholder="En quelques mots" required maxLength={150}
            value={form.title} onChange={handleChange} />
          {errors.title && <p className="field__error">{errors.title}</p>}
        </div>

        <div className="form-row">
          <div className="field">
            <label htmlFor="projectName">Projet réalisé (facultatif)</label>
            <input id="projectName" name="projectName" type="text" placeholder="Ex. : rose sur l'avant-bras" maxLength={150}
              value={form.projectName} onChange={handleChange} />
            {errors.projectName && <p className="field__error">{errors.projectName}</p>}
          </div>
          <div className="field">
            <label htmlFor="realisedAt">Date de passage (facultatif)</label>
            <input id="realisedAt" name="realisedAt" type="date" value={form.realisedAt} onChange={handleChange} />
          </div>
        </div>

        <div className="field">
          <label htmlFor="message">Votre avis</label>
          <textarea id="message" name="message" placeholder="Racontez-nous votre expérience" required maxLength={5000}
            value={form.message} onChange={handleChange} />
          {errors.message && <p className="field__error">{errors.message}</p>}
        </div>

        <div>
          <button type="submit" className="btn btn--primary" disabled={sending}>
            {sending ? 'Envoi…' : 'Publier mon avis'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CommentForm;
