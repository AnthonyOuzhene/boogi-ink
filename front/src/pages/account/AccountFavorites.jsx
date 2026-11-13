import { Link } from 'react-router';
import useApi from '../../hooks/useApi';
import { FlashGrid } from '../../components/FlashGallery';
import '../pages.scss';

/**
 * /profil/favoris : les flashs mis en favori (cœur sur la planche de flashs).
 * Les flashs gardent leur numéro de la planche complète (N°01…) : c'est ce numéro
 * qu'on indique à l'artiste pour réserver.
 */
const AccountFavorites = () => {
  // GET /api/me/favorites : uniquement MES favoris (l'API déduit l'utilisateur du jeton JWT)
  const { data: favorites, loading, error, reload } = useApi('/me/favorites');
  // Liste complète, seulement pour retrouver le numéro de chaque flash sur la planche
  const { data: allFlashes } = useApi('/flashes');

  const numberOf = (flash) => (allFlashes?.findIndex((item) => item.id === flash.id) ?? -1) + 1;
  const reservedCount = favorites?.filter((flash) => !flash.isAvailable).length ?? 0;

  return (
    <>
      <h2 className="account-section-title">Mes favoris</h2>

      {loading && !favorites && <p className="muted">Chargement…</p>}
      {error && <p className="muted">Impossible de charger tes favoris.</p>}

      {favorites?.length === 0 && (
        <div className="account-empty">
          <p className="muted">Tu n'as pas encore de flash favori. Clique sur le cœur d'un flash pour le retrouver ici.</p>
          <Link to="/flashs" className="btn btn--primary">Voir les flashs</Link>
        </div>
      )}

      {favorites?.length > 0 && (
        <>
          <p className="muted">
            Un flash te plaît vraiment ? <Link to="/contact">Écris-moi</Link> en indiquant son numéro, je te le réserve.
            {reservedCount > 0 && ` ${reservedCount === 1 ? 'Un de tes favoris est déjà réservé' : `${reservedCount} de tes favoris sont déjà réservés`}.`}
          </p>
          {/* Planche sur papier (les dessins sont sur fond blanc) ; retirer un cœur recharge la liste */}
          <div className="paper account-favorites">
            <FlashGrid flashes={favorites} numberOf={numberOf} onFavoriteChange={reload} />
          </div>
        </>
      )}
    </>
  );
};

export default AccountFavorites;
