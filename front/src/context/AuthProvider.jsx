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

  const value = useMemo(() => ({
    user,
    loading,
    isConnected: user !== null,
    isAdmin: user?.roles?.includes('ROLE_ADMIN') ?? false,
    login,
    logout,
  }), [user, loading, login, logout]);

  // React 19 : le contexte s'utilise directement comme "Provider"
  return <AuthContext value={value}>{children}</AuthContext>;
}
