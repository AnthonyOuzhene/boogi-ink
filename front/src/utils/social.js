/**
 * Liens directs vers les messageries, construits depuis les adresses des profils
 * saisies dans le backoffice (menu "Salon") :
 *   https://www.instagram.com/julienbourgarit/  →  https://ig.me/m/julienbourgarit
 *   https://www.facebook.com/julienbourgarit/   →  https://m.me/julienbourgarit
 * ig.me et m.me ouvrent directement la conversation (dans l'appli sur téléphone).
 */

/** Premier segment du chemin d'une adresse ("julienbourgarit"), ou null si l'adresse est inexploitable */
const accountFromUrl = (url) => {
  try {
    const [account] = new URL(url).pathname.split('/').filter(Boolean);
    // "profile.php" (profil Facebook sans nom d'utilisateur) : pas de lien direct possible.
    // (Les points sont autorisés : ils sont fréquents dans les noms Instagram, ex. "julien.b")
    return account && !account.endsWith('.php') ? account : null;
  } catch {
    return null;
  }
};

/** Conversation Instagram ; à défaut, le profil (le bouton "Contacter" y est) */
export const instagramMessageUrl = (profileUrl) => {
  if (!profileUrl) return null;
  const account = accountFromUrl(profileUrl);
  return account ? `https://ig.me/m/${account}` : profileUrl;
};

/** Conversation Messenger ; à défaut, la page Facebook */
export const messengerUrl = (profileUrl) => {
  if (!profileUrl) return null;
  const account = accountFromUrl(profileUrl);
  return account ? `https://m.me/${account}` : profileUrl;
};
