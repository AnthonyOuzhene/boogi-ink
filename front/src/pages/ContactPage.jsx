import { useState } from 'react';
import { Link } from 'react-router';
import { toast } from 'react-toastify';
import { FaDirections, FaFacebookMessenger, FaInstagram, FaMapMarkerAlt, FaRegCopy } from 'react-icons/fa';
import { useSalon } from '@/context/SalonContext';
import usePageTitle from '@/hooks/usePageTitle';
import { toLines } from '@/utils/format';
import { instagramMessageUrl, messengerUrl } from '@/utils/social';
import PageHero from '@/components/ui/PageHero';
import './ContactPage.scss';

// Ce que l'artiste a besoin de savoir pour répondre (affiché en liste, et repris dans le modèle)
const CHECKLIST = [
  'Ton idée ou le style qui te plaît',
  "L'emplacement sur le corps",
  'La taille approximative',
  'Tes références : photos, dessins, inspirations',
  'Tes disponibilités',
];

// Modèle à coller dans la messagerie (Instagram et Messenger ne permettent pas de pré-remplir un message)
const MESSAGE_TEMPLATE = `Salut ! J'ai un projet de tatouage :
- Mon idée / le style :
- L'emplacement :
- La taille approximative :
- Mes références : (photos jointes)
- Mes disponibilités : `;

/**
 * Page Contact : pas de formulaire, l'artiste répond lui-même sur Instagram et Messenger.
 * La page explique quoi envoyer avant d'ouvrir la messagerie (moins d'allers-retours).
 * Coordonnées, horaires et réseaux : backoffice, menu "Salon".
 */
const ContactPage = () => {
  usePageTitle('Contact');
  const { salon } = useSalon();
  const fullAddress = salon ? `${salon.address}, ${salon.zipCode} ${salon.city}` : null;
  const instagram = instagramMessageUrl(salon?.instagramUrl);
  const messenger = messengerUrl(salon?.facebookUrl);
  // Carte Google chargée seulement au clic : Google peut déposer des cookies, il faut donc
  // l'accord du visiteur (CNIL). Son clic sur "Afficher la carte" vaut consentement.
  const [showMap, setShowMap] = useState(false);

  const copyTemplate = async () => {
    try {
      await navigator.clipboard.writeText(MESSAGE_TEMPLATE);
      toast.success('Message copié ! Colle-le dans ta conversation et complète-le.');
    } catch {
      // Presse-papier refusé (ancien navigateur, page non sécurisée…)
      toast.error("La copie n'a pas marché : recopie simplement la liste ci-dessus.");
    }
  };

  return (
    <>
      <PageHero
        eyebrow="Prendre rendez-vous"
        title={<>Par<span className="text-cyan">lons-en</span></>}
        lead="Le plus simple : écris-moi sur Instagram ou sur Messenger, je te réponds moi-même pour qu'on parle de ton projet."
      />

      <section className="container section section--tight contact">
        <div className="neon-card contact__dm">
          {(instagram || messenger) ? (
            <>
              <h2 className="contact__title">Écris-moi</h2>
              <div className="dm-buttons">
                {instagram && (
                  <a href={instagram} target="_blank" rel="noreferrer" className="dm-btn dm-btn--instagram">
                    <FaInstagram size={24} aria-hidden="true" /> Écrire sur Instagram
                  </a>
                )}
                {messenger && (
                  <a href={messenger} target="_blank" rel="noreferrer" className="dm-btn dm-btn--messenger">
                    <FaFacebookMessenger size={24} aria-hidden="true" /> Écrire sur Messenger
                  </a>
                )}
              </div>
            </>
          ) : (
            // Aucun réseau renseigné dans le backoffice : on renvoie vers le téléphone
            // (pas de contact par e-mail : l'artiste répond sur les messageries)
            <h2 className="contact__title">Appelle le salon</h2>
          )}

          <div className="contact__checklist">
            <h3 className="contact__label contact__label--pink">Dans ton message, indique-moi</h3>
            <ul>
              {CHECKLIST.map((item) => <li key={item}>{item}</li>)}
            </ul>
            <p className="muted">Pour un flash, donne-moi simplement son numéro (N°01, N°02…).</p>
            {/* Le bouton copie un texte à trous : il faut dire ce qu'on en fait ensuite */}
            <p className="contact__copy-hint">
              Pour gagner du temps : copie ce message tout prêt, colle-le dans ta conversation
              Instagram ou Messenger, puis complète-le.
            </p>
            <button type="button" className="btn contact__copy" onClick={copyTemplate}>
              <FaRegCopy aria-hidden="true" /> Copier un message tout prêt
            </button>
          </div>
        </div>

        <div className="contact__info">
          {salon && (
            <>
              <div>
                <h2 className="contact__label">Adresse</h2>
                <p>{salon.address}<br />{salon.zipCode} {salon.city}</p>
                {!salon.isOpen && <p className="field__error">Le salon est actuellement fermé.</p>}
              </div>
              <div>
                <h2 className="contact__label">Téléphone du salon</h2>
                <p><a href={`tel:${salon.phoneNumber.replace(/\s/g, '')}`}>{salon.phoneNumber}</a></p>
              </div>
            </>
          )}
          {salon?.openingHours && (
            <div>
              <h2 className="contact__label">Horaires</h2>
              <p className="contact__hours">
                {toLines(salon.openingHours).map((line) => <span key={line}>{line}</span>)}
              </p>
            </div>
          )}
          {/* La FAQ n'est plus dans le menu : on la rappelle ici, là où l'on se pose des questions */}
          <div>
            <h2 className="contact__label">Une question ?</h2>
            <p>La réponse est peut-être dans la <Link to="/faq">FAQ</Link>.</p>
          </div>
        </div>
      </section>

      {fullAddress && (
        <section className="container section">
          {showMap ? (
            <iframe
              className="contact__map"
              title="Plan d'accès au salon"
              src={`https://maps.google.com/maps?q=${encodeURIComponent(fullAddress)}&output=embed`}
            />
          ) : (
            // Avant le clic : rien n'est chargé depuis Google
            <div className="contact__map-placeholder">
              <FaMapMarkerAlt className="contact__map-pin" aria-hidden="true" />
              <p className="contact__map-address">{fullAddress}</p>
              <div className="contact__map-actions">
                <button type="button" className="btn btn--primary" onClick={() => setShowMap(true)}>
                  Afficher la carte
                </button>
                {/* L'itinéraire s'ouvre directement dans Google Maps (nouvel onglet) */}
                <a
                  href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress)}`}
                  target="_blank"
                  rel="noreferrer"
                  className="btn btn--ghost"
                >
                  <FaDirections aria-hidden="true" /> Itinéraire
                </a>
              </div>
              <p className="contact__map-note">
                La carte est fournie par Google, qui peut déposer des cookies : elle ne se charge que si tu cliques.
              </p>
            </div>
          )}
        </section>
      )}
    </>
  );
};

export default ContactPage;
