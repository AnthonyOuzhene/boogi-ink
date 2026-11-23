import axios from 'axios';

export const BACKEND_URL = import.meta.env.VITE_BACKEND_URL;

/**
 * Instance axios partagée par toute l'application :
 * - l'URL de base évite de répéter "https://.../api" partout ;
 * - "Accept: application/json" demande du JSON simple (et pas du JSON-LD) à API Platform.
 */
const api = axios.create({
  baseURL: `${BACKEND_URL}/api`,
  headers: { Accept: 'application/json' },
});

export default api;

/** Les images sont servies par Symfony : l'API renvoie "/uploads/...", on ajoute le domaine. */
export const assetUrl = (path) => (path ? `${BACKEND_URL}${path}` : null);

/**
 * Transforme une erreur de validation (HTTP 422) en objet
 * { authorName: "Indique ton prénom…", message: "…" } facile à afficher sous chaque champ.
 *
 * Deux formats possibles selon qui répond :
 * - API Platform : le texte est dans "message" ;
 * - contrôleur Symfony avec #[MapRequestPayload] (ex. POST /api/comments) : le texte est dans "title".
 */
export function getViolations(error) {
  const violations = error?.response?.data?.violations ?? [];
  return Object.fromEntries(violations.map((v) => [v.propertyPath, v.message ?? v.title]));
}
