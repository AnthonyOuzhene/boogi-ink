import { useEffect } from 'react';
import { useLocation } from 'react-router';

/**
 * /atelier#seance → défile jusqu'à id="seance". Le navigateur ne peut pas le faire seul :
 * les sections n'existent qu'une fois les données de l'API reçues, d'où "ready".
 */
export default function useScrollToHash(ready) {
  const { hash } = useLocation();

  useEffect(() => {
    if (!ready || !hash) return;
    document.getElementById(decodeURIComponent(hash.slice(1)))?.scrollIntoView();
  }, [ready, hash]);
}
