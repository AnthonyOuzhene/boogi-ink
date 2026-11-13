import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import { useAuth } from '../context/AuthContext';
import './pages.scss';

const LoginPage = () => {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [sending, setSending] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSending(true);
    try {
      const user = await login(email, password);
      toast.success(`Bonjour ${user.name} !`);
      navigate('/'); // redirection sans rechargement de page
    } catch {
      toast.error('Email ou mot de passe incorrect.');
    } finally {
      setSending(false);
    }
  };

  return (
    <section className="container auth">
      <div className="neon-card auth__card">
        <p className="eyebrow">Espace client</p>
        <h1 className="section-title">Connexion</h1>

        <form className="form" onSubmit={handleSubmit}>
          <div className="field">
            <label htmlFor="email">E-mail</label>
            <input id="email" type="email" required autoComplete="email" placeholder="toi@exemple.fr"
              value={email} onChange={(e) => setEmail(e.target.value)} />
          </div>
          <div className="field">
            <label htmlFor="password">Mot de passe</label>
            <input id="password" type="password" required autoComplete="current-password"
              value={password} onChange={(e) => setPassword(e.target.value)} />
          </div>
          <button type="submit" className="btn btn--primary" disabled={sending}>
            {sending ? 'Connexion…' : 'Se connecter'}
          </button>
        </form>

        <p className="auth__switch muted">
          Pas encore de compte ? <Link to="/inscription">Inscris-toi</Link>
        </p>
      </div>
    </section>
  );
};

export default LoginPage;
