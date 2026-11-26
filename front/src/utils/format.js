const dateFormatter = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
const priceFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
const roundPriceFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });

/** "2026-11-01T10:00:00+01:00" → "1 novembre 2026" */
export const formatDate = (isoDate) => (isoDate ? dateFormatter.format(new Date(isoDate)) : '');

/** "80.00" → "80 €" ; "49.50" → "49,50 €" ; absent (l'API omet les null) → "Sur devis" */
export const formatPrice = (price) => {
  if (!price) return 'Sur devis';
  const amount = Number(price);
  return (Number.isInteger(amount) ? roundPriceFormatter : priceFormatter).format(amount);
};

/** Format : une ligne vide = nouveau paragraphe */
export const toParagraphs = (text) =>
  (text ?? '')
    .split(/\n\s*\n/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean);

/** Format : une ligne vide = paragraphe, "## " en début de bloc = intertitre */
export const toBlocks = (text) =>
  toParagraphs(text).map((block) =>
    block.startsWith('## ')
      ? { type: 'heading', text: block.slice(3).trim() }
      : { type: 'paragraph', text: block });

/** "Mardi – Samedi : 10h – 19h\nLundi : fermé" → ["Mardi – Samedi : 10h – 19h", "Lundi : fermé"] */
export const toLines = (text) =>
  (text ?? '').split('\n').map((line) => line.trim()).filter(Boolean);
