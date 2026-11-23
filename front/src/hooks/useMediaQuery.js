import { useEffect, useState } from 'react';

/**
 * Indique si une media query CSS correspond à l'écran, et se met à jour quand ça change
 * (fenêtre redimensionnée, téléphone tourné).
 * Usage : const isMobile = useMediaQuery('(max-width: 700px)');
 */
export default function useMediaQuery(query) {
  const [matches, setMatches] = useState(() => window.matchMedia(query).matches);

  useEffect(() => {
    const media = window.matchMedia(query);
    const onChange = () => setMatches(media.matches);
    onChange(); // la query a pu changer entre le premier rendu et cet effet
    media.addEventListener('change', onChange);
    return () => media.removeEventListener('change', onChange);
  }, [query]);

  return matches;
}
