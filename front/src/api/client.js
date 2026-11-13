import axios from 'axios';

export const BACKEND_URL = import.meta.env.VITE_BACKEND_URL;

const TOKEN_KEY = 'token';
export const getToken = () => sessionStorage.getItem(TOKEN_KEY);
export const setToken = (token) => sessionStorage.setItem(TOKEN_KEY, token);
export const clearToken = () => sessionStorage.removeItem(TOKEN_KEY);

/**
 * Instance axios partagée par toute l'application :
 * - l'URL de base évite de répéter "https://.../api" partout ;
 * - "Accept: application/json" demande du JSON simple (et pas du JSON-LD) à API Platform.
 */
const api = axios.create({
  baseURL: `${BACKEND_URL}/api`,
  headers: { Accept: 'application/json' },
});

// "Intercepteur" : exécuté avant CHAQUE requête, il ajoute le jeton JWT s'il existe
api.interceptors.request.use((config) => {
  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

/** Les images sont servies par Symfony : l'API renvoie "/uploads/...", on ajoute le domaine. */
export const assetUrl = (path) => (path ? `${BACKEND_URL}${path}` : null);

/**
 * Transforme une erreur de validation (HTTP 422) en objet
 * { email: "Un compte existe déjà...", plainPassword: "..." } facile à afficher sous chaque champ.
 *
 * Deux formats possibles selon qui répond :
 * - API Platform (ressources /api/users, /api/comments…) : le texte est dans "message" ;
 * - contrôleur Symfony avec #[MapRequestPayload] (ex. /api/me/password) : le texte est dans "title".
 */
export function getViolations(error) {
  const violations = error?.response?.data?.violations ?? [];
  return Object.fromEntries(violations.map((v) => [v.propertyPath, v.message ?? v.title]));
}
