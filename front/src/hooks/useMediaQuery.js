import { useEffect, useState } from 'react';

/** Usage : const isMobile = useMediaQuery('(max-width: 700px)'); — suit le redimensionnement */
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
