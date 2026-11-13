import { useCallback } from 'react';
import { useLocation, useNavigate } from 'react-router';
import { toast } from 'react-toastify';
import { useAuth } from '../context/AuthContext';

/**
 * Suite d'une inscription ou d'une connexion lancée depuis un cœur de flash.
 * Le cœur envoie vers /inscription avec, dans l'"état" de la navigation (location.state) :
 *   - from : la page à retrouver ensuite (ex. "/flashs") ;
 *   - favoriteFlash : le flash cliqué ({ id, name }), à ajouter aux favoris.
 *
 * Usage (LoginPage, SignUpPage) :
 *   const { pendingFavorite, authState, finishAuth } = useAuthRedirect();
 *   … après la connexion réussie : await finishAuth();
 * authState se transmet au lien vers l'autre page (inscription ↔ connexion) pour ne rien perdre.
 */
export default function useAuthRedirect() {
  const { addFavoriteFlash } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const authState = location.state ?? null;
  const pendingFavorite = authState?.favoriteFlash ?? null;

  const finishAuth = useCallback(async () => {
    if (pendingFavorite) {
      try {
        await addFavoriteFlash(pendingFavorite.id);
        toast.success(`« ${pendingFavorite.name} » est dans tes favoris ♥`);
      } catch {
        toast.error("Le flash n'a pas pu être ajouté à tes favoris, réessaie depuis la planche.");
      }
    }
    // replace : la page de connexion / d'inscription ne reste pas dans l'historique
    navigate(authState?.from ?? '/', { replace: true });
  }, [addFavoriteFlash, authState, navigate, pendingFavorite]);

  return { pendingFavorite, authState, finishAuth };
}
