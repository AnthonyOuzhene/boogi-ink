import { createContext, useContext } from 'react';

/**
 * Remplace Redux : un "Context" React rend l'utilisateur connecté accessible
 * à n'importe quel composant, sans le passer de parent en enfant.
 * La valeur est fournie par <AuthProvider> (AuthProvider.jsx).
 */
export const AuthContext = createContext(null);

/**
 * const { user, isConnected, isAdmin, login, logout } = useAuth();
 * Favoris : isFavoriteFlash(id) et toggleFavoriteFlash(id) (voir AuthProvider.jsx)
 */
export const useAuth = () => useContext(AuthContext);
