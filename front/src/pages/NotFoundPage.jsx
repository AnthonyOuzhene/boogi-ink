import { Link } from 'react-router';
import usePageTitle from '@/hooks/usePageTitle';
import badTattoo from '@/assets/images/bad-tattoo.jpg';
import './NotFoundPage.scss';

const NotFoundPage = () => {
  usePageTitle('Page introuvable');

  return (
    <section className="container section not-found">
      <div className="not-found__text">
        <p className="not-found__code glow">404</p>
        <h1 className="section-title">Cette page<br />n'existe pas</h1>
        <p className="muted">Comme ce tatouage, certaines choses ne devraient pas exister. Choisis plutôt un pro.</p>
        <Link to="/" className="btn btn--primary">Retour à l'accueil</Link>
      </div>
      <img src={badTattoo} alt="Un tatouage raté" className="not-found__img" />
    </section>
  );
};

export default NotFoundPage;
