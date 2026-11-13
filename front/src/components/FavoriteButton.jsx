import { useLocation, useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import { FaHeart, FaRegHeart } from 'react-icons/fa';
import { useAuth } from '../context/AuthContext';
import './FavoriteButton.scss';

/**
 * Cœur "mettre en favori" sur un flash.
 * - connecté : ajoute / retire le flash de "Mes favoris" (cœur plein = favori) ;
 * - pas connecté : envoie vers l'inscription (lien vers la connexion sur place) ; une fois
 *   connecté, le flash est ajouté aux favoris et on revient sur cette page (useAuthRedirect).
 * onChange (facultatif) : appelé après un changement réussi (ex. recharger "Mes favoris").
 */
const FavoriteButton = ({ flash, onChange }) => {
  const { isConnected, isFavoriteFlash, toggleFavoriteFlash } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const favorite = isConnected && isFavoriteFlash(flash.id);

  const handleClick = async () => {
    if (!isConnected) {
      // La plupart des visiteurs n'ont pas encore de compte : direction l'inscription.
      // "from" = page à retrouver ensuite, "favoriteFlash" = flash à ajouter aux favoris
      navigate('/inscription', {
        state: { from: location.pathname + location.search, favoriteFlash: { id: flash.id, name: flash.name } },
      });
      return;
    }
    try {
      const added = await toggleFavoriteFlash(flash.id);
      if (added) toast.success('Ajouté à tes favoris ♥');
      onChange?.();
    } catch {
      toast.error("Le favori n'a pas pu être enregistré, réessaie.");
    }
  };

  return (
    <button
      type="button"
      className={`favorite-btn ${favorite ? 'is-active' : ''}`}
      aria-pressed={favorite}
      aria-label={favorite ? `Retirer « ${flash.name} » de mes favoris` : `Ajouter « ${flash.name} » à mes favoris`}
      title={favorite ? 'Retirer de mes favoris' : 'Ajouter à mes favoris'}
      onClick={handleClick}
    >
      {favorite ? <FaHeart aria-hidden="true" /> : <FaRegHeart aria-hidden="true" />}
    </button>
  );
};

export default FavoriteButton;
