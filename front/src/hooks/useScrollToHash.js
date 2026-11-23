import { useEffect } from 'react';
import { useLocation } from 'react-router';

/**
 * Fait défiler la page jusqu'à l'élément visé par le hash de l'URL (/atelier#seance → id="seance").
 * Le navigateur le fait tout seul pour une page statique, mais ici les sections n'existent
 * qu'une fois les données de l'API reçues : on attend donc que "ready" passe à true.
 */
export default function useScrollToHash(ready) {
  const { hash } = useLocation();

  useEffect(() => {
    if (!ready || !hash) return;
    document.getElementById(decodeURIComponent(hash.slice(1)))?.scrollIntoView();
  }, [ready, hash]);
}
