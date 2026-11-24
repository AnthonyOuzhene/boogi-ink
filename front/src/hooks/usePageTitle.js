import { useEffect } from 'react';

const SITE_NAME = "Boogi'ink";

/**
 * Titre de l'onglet du navigateur pour la page affichée : "Livre d'or — Boogi'ink".
 * Accessibilité (WCAG 2.4.2) : c'est la première chose qu'annonce un lecteur d'écran
 * en arrivant sur une page ; c'est aussi le titre affiché par les moteurs de recherche.
 * Sans titre (accueil, ou page encore en chargement) : "Boogi'ink — Salon de tatouage".
 */
export default function usePageTitle(title) {
  useEffect(() => {
    document.title = title ? `${title} — ${SITE_NAME}` : `${SITE_NAME} — Salon de tatouage`;
  }, [title]);
}
