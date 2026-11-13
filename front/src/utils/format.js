const dateFormatter = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
const priceFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
// Prix rond : sans centimes inutiles ("150 €" plutôt que "150,00 €")
const roundPriceFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });

/** "2026-11-01T10:00:00+01:00" → "1 novembre 2026" */
export const formatDate = (isoDate) => (isoDate ? dateFormatter.format(new Date(isoDate)) : '');

/**
 * "80.00" → "80 €" ; "49.50" → "49,50 €" (centimes seulement s'il y en a) ;
 * prix absent (l'API omet les valeurs null) → "Sur devis"
 */
export const formatPrice = (price) => {
  if (!price) return 'Sur devis';
  const amount = Number(price);
  return (Number.isInteger(amount) ? roundPriceFormatter : priceFormatter).format(amount);
};

/** Texte saisi dans le backoffice → liste de paragraphes (une ligne vide = nouveau paragraphe) */
export const toParagraphs = (text) =>
  (text ?? '')
    .split(/\n\s*\n/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean);

/**
 * Contenu d'une page (backoffice) → blocs à afficher :
 * une ligne commençant par "## " devient un intertitre, le reste des paragraphes.
 */
export const toBlocks = (text) =>
  toParagraphs(text).map((block) =>
    block.startsWith('## ')
      ? { type: 'heading', text: block.slice(3).trim() }
      : { type: 'paragraph', text: block });

/** "Mardi – Samedi : 10h – 19h\nLundi : fermé" → ["Mardi – Samedi : 10h – 19h", "Lundi : fermé"] */
export const toLines = (text) =>
  (text ?? '').split('\n').map((line) => line.trim()).filter(Boolean);
