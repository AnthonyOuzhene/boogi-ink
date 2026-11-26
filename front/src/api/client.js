import axios from 'axios';

export const BACKEND_URL = import.meta.env.VITE_BACKEND_URL;

// "Accept: application/json" : API Platform renvoie alors du JSON simple au lieu du JSON-LD
const api = axios.create({
  baseURL: `${BACKEND_URL}/api`,
  headers: { Accept: 'application/json' },
});

export default api;

/** Les images sont servies par Symfony : l'API renvoie "/uploads/...", on ajoute le domaine. */
export const assetUrl = (path) => (path ? `${BACKEND_URL}${path}` : null);

/**
 * Erreur 422 → { authorName: "Indique ton prénom…", … } à afficher sous chaque champ.
 * Le texte est dans "message" (API Platform) ou "title" (contrôleur avec #[MapRequestPayload], ex. POST /api/comments).
 */
export function getViolations(error) {
  const violations = error?.response?.data?.violations ?? [];
  return Object.fromEntries(violations.map((v) => [v.propertyPath, v.message ?? v.title]));
}
