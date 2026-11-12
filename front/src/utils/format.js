const dateFormatter = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
const priceFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

/** "2026-11-01T10:00:00+01:00" → "1 novembre 2026" */
export const formatDate = (isoDate) => (isoDate ? dateFormatter.format(new Date(isoDate)) : '');

/** "80.00" → "80,00 €" ; prix absent (l'API omet les valeurs null) → "Sur devis" */
export const formatPrice = (price) => (price ? priceFormatter.format(Number(price)) : 'Sur devis');

/** Texte saisi dans le backoffice → liste de paragraphes (une ligne vide = nouveau paragraphe) */
export const toParagraphs = (text) =>
  (text ?? '')
    .split(/\n\s*\n/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean);
