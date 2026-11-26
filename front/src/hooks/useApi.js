import { useCallback, useEffect, useState } from 'react';
import api from '@/api/client';

/**
 * Usage : const { data, loading, error, reload } = useApi('/flashes');
 * La requête est relancée quand "url" change (ex. filtre) ou via reload().
 */
export default function useApi(url) {
  // On mémorise POUR QUELLE url on a reçu une réponse
  const [result, setResult] = useState({ url: null, data: null, error: null });
  // reload() incrémente ce compteur, ce qui relance l'effet
  const [reloadCount, setReloadCount] = useState(0);

  useEffect(() => {
    // Si le composant disparaît (ou si l'url change) avant la réponse, on ignore le résultat
    let cancelled = false;

    api.get(url)
      .then((response) => { if (!cancelled) setResult({ url, data: response.data, error: null }); })
      .catch((error) => { if (!cancelled) setResult({ url, data: null, error }); });

    return () => { cancelled = true; };
  }, [url, reloadCount]);

  const reload = useCallback(() => setReloadCount((count) => count + 1), []);

  // "loading" n'est pas un état : il se déduit (la dernière réponse ne correspond pas encore à l'url demandée).
  // Pendant un rechargement, on garde les anciennes données affichées pour éviter un écran vide.
  return {
    data: result.data,
    loading: result.url !== url,
    error: result.url === url ? result.error : null,
    reload,
  };
}
