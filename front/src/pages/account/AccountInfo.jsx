import { Link } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import '../pages.scss';

/** /profil : informations du compte */
const AccountInfo = () => {
  // Le gabarit (AccountLayout) garantit qu'on est connecté : user n'est jamais null ici
  const { user } = useAuth();

  return (
    <>
      <h2 className="account-section-title">Mes informations</h2>
      <dl className="account-info">
        <div><dt>Pseudo</dt><dd>{user.name}</dd></div>
        <div><dt>E-mail</dt><dd>{user.email}</dd></div>
      </dl>
      <div>
        <Link to="/livre-d-or" className="btn btn--primary">Laisser un avis</Link>
      </div>
    </>
  );
};

export default AccountInfo;
