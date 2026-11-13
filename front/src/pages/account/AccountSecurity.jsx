import { useState } from 'react';
import { toast } from 'react-toastify';
import api, { getViolations } from '../../api/client';

const EMPTY_FORM = { currentPassword: '', newPassword: '', confirmPassword: '' };

/** /profil/securite : changement de mot de passe */
const AccountSecurity = () => {
  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});

    // Vérification côté navigateur (confort) ; le serveur revérifie tout le reste
    if (form.newPassword !== form.confirmPassword) {
      setErrors({ confirmPassword: 'Les deux mots de passe ne correspondent pas.' });
      return;
    }

    setSending(true);
    try {
      // POST /api/me/password (AccountController) → 204 si tout est bon, 422 avec les erreurs sinon
      await api.post('/me/password', {
        currentPassword: form.currentPassword,
        newPassword: form.newPassword,
      });
      toast.success('Votre mot de passe a été modifié.');
      setForm(EMPTY_FORM);
    } catch (error) {
      setErrors(getViolations(error));
      toast.error("Le mot de passe n'a pas été modifié, vérifiez le formulaire.");
    } finally {
      setSending(false);
    }
  };

  return (
    <>
      <h2 className="account-section-title">Sécurité</h2>
      <p className="muted">Modifiez votre mot de passe. Il doit contenir au moins 8 caractères.</p>

      <form className="form account-form" onSubmit={handleSubmit}>
        <div className="field">
          <label htmlFor="currentPassword">Mot de passe actuel</label>
          <input id="currentPassword" name="currentPassword" type="password" required autoComplete="current-password"
            value={form.currentPassword} onChange={handleChange} />
          {errors.currentPassword && <p className="field__error">{errors.currentPassword}</p>}
        </div>

        <div className="field">
          <label htmlFor="newPassword">Nouveau mot de passe</label>
          <input id="newPassword" name="newPassword" type="password" required minLength={8} autoComplete="new-password"
            value={form.newPassword} onChange={handleChange} />
          {errors.newPassword && <p className="field__error">{errors.newPassword}</p>}
        </div>

        <div className="field">
          <label htmlFor="confirmPassword">Confirmer le nouveau mot de passe</label>
          <input id="confirmPassword" name="confirmPassword" type="password" required autoComplete="new-password"
            value={form.confirmPassword} onChange={handleChange} />
          {errors.confirmPassword && <p className="field__error">{errors.confirmPassword}</p>}
        </div>

        <div>
          <button type="submit" className="btn btn--primary" disabled={sending}>
            {sending ? 'Enregistrement…' : 'Modifier mon mot de passe'}
          </button>
        </div>
      </form>
    </>
  );
};

export default AccountSecurity;
