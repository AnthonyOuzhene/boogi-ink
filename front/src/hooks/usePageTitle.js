import { useEffect } from 'react';

const SITE_NAME = "Boogi'ink";

/**
 * WCAG 2.4.2 : le titre est la première chose qu'annonce un lecteur d'écran sur une page
 * (et celui qu'affichent les moteurs de recherche).
 */
export default function usePageTitle(title) {
  useEffect(() => {
    document.title = title ? `${title} — ${SITE_NAME}` : `${SITE_NAME} — Salon de tatouage`;
  }, [title]);
}
