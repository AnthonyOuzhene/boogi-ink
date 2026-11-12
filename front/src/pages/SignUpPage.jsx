import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import api, { getViolations } from '../api/client';
import { useAuth } from '../context/AuthContext';
import './pages.scss';

const EMPTY_FORM = { name: '', email: '', password: '', passwordConfirm: '' };

const SignUpPage = () => {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState({});
  const [sending, setSending] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});

    // Vérification côté navigateur (confort) ; le back revérifie tout de toute façon
    if (form.password !== form.passwordConfirm) {
      setErrors({ passwordConfirm: 'Les mots de passe ne correspondent pas.' });
      return;
    }

    setSending(true);
    try {
      await api.post('/users', { name: form.name, email: form.email, plainPassword: form.password });
      // Inscription réussie : on connecte directement l'utilisateur
      await login(form.email, form.password);
      toast.success('Bienvenue ! Votre compte est créé.');
      navigate('/');
    } catch (error) {
      setErrors(getViolations(error));
      toast.error("L'inscription a échoué, vérifiez le formulaire.");
    } finally {
      setSending(false);
    }
  };

  return (
    <section className="container auth">
      <div className="neon-card auth__card">
        <p className="eyebrow">Espace client</p>
        <h1 className="section-title">Inscription</h1>

        <form className="form" onSubmit={handleSubmit}>
          <div className="field">
            <label htmlFor="name">Pseudo</label>
            <input id="name" name="name" type="text" required maxLength={100} autoComplete="nickname"
              value={form.name} onChange={handleChange} />
            {errors.name && <p className="field__error">{errors.name}</p>}
          </div>
          <div className="field">
            <label htmlFor="email">E-mail</label>
            <input id="email" name="email" type="email" required autoComplete="email" placeholder="vous@exemple.fr"
              value={form.email} onChange={handleChange} />
            {errors.email && <p className="field__error">{errors.email}</p>}
          </div>
          <div className="field">
            <label htmlFor="password">Mot de passe (8 caractères minimum)</label>
            <input id="password" name="password" type="password" required minLength={8} autoComplete="new-password"
              value={form.password} onChange={handleChange} />
            {errors.plainPassword && <p className="field__error">{errors.plainPassword}</p>}
          </div>
          <div className="field">
            <label htmlFor="passwordConfirm">Confirmer le mot de passe</label>
            <input id="passwordConfirm" name="passwordConfirm" type="password" required autoComplete="new-password"
              value={form.passwordConfirm} onChange={handleChange} />
            {errors.passwordConfirm && <p className="field__error">{errors.passwordConfirm}</p>}
          </div>
          <button type="submit" className="btn btn--primary" disabled={sending}>
            {sending ? 'Inscription…' : 'Créer mon compte'}
          </button>
        </form>

        <p className="auth__switch muted">
          Déjà inscrit ? <Link to="/connexion">Connectez-vous</Link>
        </p>
      </div>
    </section>
  );
};

export default SignUpPage;
