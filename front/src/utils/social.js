/**
 * Profil → lien direct vers la conversation (ouvre l'appli sur téléphone) :
 *   https://www.instagram.com/julienbourgarit/  →  https://ig.me/m/julienbourgarit
 *   https://www.facebook.com/julienbourgarit/   →  https://m.me/julienbourgarit
 */

const accountFromUrl = (url) => {
  try {
    const [account] = new URL(url).pathname.split('/').filter(Boolean);
    // "profile.php" = profil Facebook sans nom d'utilisateur, pas de lien direct possible
    // (on teste ".php" et pas "." : les points sont fréquents dans les noms Instagram)
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
