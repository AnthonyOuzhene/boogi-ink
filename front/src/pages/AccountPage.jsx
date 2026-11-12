import { Link, Navigate } from 'react-router';
import { useAuth } from '../context/AuthContext';
import PageHero from '../components/PageHero';
import './pages.scss';

const AccountPage = () => {
  const { user, loading } = useAuth();

  // Au chargement de la page, on attend de savoir si le jeton est encore valide
  if (loading) return null;
  // Page réservée : sans connexion, redirection vers /connexion
  if (!user) return <Navigate to="/connexion" replace />;

  return (
    <>
      <PageHero eyebrow="Mon compte" title={<>Bonjour <span className="text-cyan">{user.name}</span></>} />
      <section className="container section section--tight">
        <dl className="account-info">
          <div><dt>Pseudo</dt><dd>{user.name}</dd></div>
          <div><dt>E-mail</dt><dd>{user.email}</dd></div>
        </dl>
        <div className="detail-actions">
          <Link to="/livre-d-or" className="btn btn--primary">Laisser un avis</Link>
        </div>
      </section>
    </>
  );
};

export default AccountPage;
