import { useEffect, useState } from 'react';
import api from '../api/client';

/**
 * Hook maison pour charger des données en GET.
 * Usage : const { data, loading, error } = useApi('/services');
 * Quand "url" change (ex. filtre de catégorie), la requête est relancée.
 */
export default function useApi(url) {
  // On mémorise POUR QUELLE url on a reçu une réponse
  const [result, setResult] = useState({ url: null, data: null, error: null });

  useEffect(() => {
    // Si le composant disparaît (ou si l'url change) avant la réponse, on ignore le résultat
    let cancelled = false;

    api.get(url)
      .then((response) => { if (!cancelled) setResult({ url, data: response.data, error: null }); })
      .catch((error) => { if (!cancelled) setResult({ url, data: null, error }); });

    return () => { cancelled = true; };
  }, [url]);

  // "loading" n'est pas un état : il se déduit (la dernière réponse ne correspond pas encore à l'url demandée).
  // Pendant un rechargement, on garde les anciennes données affichées pour éviter un écran vide.
  return {
    data: result.data,
    loading: result.url !== url,
    error: result.url === url ? result.error : null,
  };
}
