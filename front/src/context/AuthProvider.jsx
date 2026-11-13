import { useCallback, useEffect, useMemo, useState } from 'react';
import api, { clearToken, getToken, setToken } from '../api/client';
import { AuthContext } from './AuthContext';

/** Fournit l'utilisateur connecté et les actions login/logout à toute l'application */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  // Au démarrage, s'il reste un jeton, on attend la réponse de /api/me avant de décider
  const [loading, setLoading] = useState(() => getToken() !== null);

  const logout = useCallback(() => {
    clearToken();
    setUser(null);
  }, []);

  // Recharge de la page : le jeton est encore là, on récupère le profil
  useEffect(() => {
    if (!getToken()) return;
    api.get('/me')
      .then((response) => setUser(response.data))
      .catch(() => clearToken()) // jeton expiré ou invalide
      .finally(() => setLoading(false));
  }, []);

  // Jeton expiré en cours de navigation (le JWT dure 1 h) : l'API répond 401 → on déconnecte
  useEffect(() => {
    const id = api.interceptors.response.use(undefined, (error) => {
      if (error.response?.status === 401 && error.config?.headers?.Authorization) {
        logout();
      }
      return Promise.reject(error);
    });
    return () => api.interceptors.response.eject(id);
  }, [logout]);

  const login = useCallback(async (email, password) => {
    const { data } = await api.post('/login', { email, password });
    setToken(data.token);
    const me = await api.get('/me');
    setUser(me.data);
    return me.data;
  }, []);

  // --- Flashs favoris ---
  // GET /api/me renvoie favoriteFlashIds : on les garde dans "user", tous les cœurs du site
  // (planche de flashs, accueil, Mes favoris) lisent donc la même liste.
  const isFavoriteFlash = useCallback(
    (flashId) => user?.favoriteFlashIds?.includes(flashId) ?? false,
    [user],
  );

  // Mise à jour "optimiste" : le cœur change tout de suite, puis l'API confirme ;
  // en cas d'erreur, on remet l'ancien état (et l'appelant affiche un message)
  const toggleFavoriteFlash = useCallback(async (flashId) => {
    const wasFavorite = isFavoriteFlash(flashId);
    const setIds = (change) => setUser((current) => current && {
      ...current,
      favoriteFlashIds: change(current.favoriteFlashIds ?? []),
    });
    const add = (ids) => [...ids, flashId];
    const remove = (ids) => ids.filter((id) => id !== flashId);

    setIds(wasFavorite ? remove : add);
    try {
      if (wasFavorite) await api.delete(`/me/favorites/${flashId}`);
      else await api.put(`/me/favorites/${flashId}`);
    } catch (error) {
      setIds(wasFavorite ? add : remove);
      throw error;
    }
    return !wasFavorite; // true = vient d'être ajouté
  }, [isFavoriteFlash]);

  // Ajout seul (jamais de retrait), utilisé juste après une inscription / connexion
  // lancée depuis un cœur : si le flash est déjà favori, rien ne change (PUT idempotent)
  const addFavoriteFlash = useCallback(async (flashId) => {
    await api.put(`/me/favorites/${flashId}`);
    setUser((current) => current && {
      ...current,
      favoriteFlashIds: (current.favoriteFlashIds ?? []).includes(flashId)
        ? current.favoriteFlashIds
        : [...(current.favoriteFlashIds ?? []), flashId],
    });
  }, []);

  const value = useMemo(() => ({
    user,
    loading,
    isConnected: user !== null,
    isAdmin: user?.roles?.includes('ROLE_ADMIN') ?? false,
    login,
    logout,
    isFavoriteFlash,
    toggleFavoriteFlash,
    addFavoriteFlash,
  }), [user, loading, login, logout, isFavoriteFlash, toggleFavoriteFlash, addFavoriteFlash]);

  // React 19 : le contexte s'utilise directement comme "Provider"
  return <AuthContext value={value}>{children}</AuthContext>;
}
