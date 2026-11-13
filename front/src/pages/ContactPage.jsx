import { Link } from 'react-router';
import { toast } from 'react-toastify';
import { FaFacebookMessenger, FaInstagram, FaRegCopy } from 'react-icons/fa';
import { useSalon } from '../context/SalonContext';
import { toLines } from '../utils/format';
import { instagramMessageUrl, messengerUrl } from '../utils/social';
import PageHero from '../components/PageHero';
import './pages.scss';

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
  const { salon } = useSalon();
  const fullAddress = salon ? `${salon.address}, ${salon.zipCode} ${salon.city}` : null;
  const instagram = instagramMessageUrl(salon?.instagramUrl);
  const messenger = messengerUrl(salon?.facebookUrl);

  const copyTemplate = async () => {
    try {
      await navigator.clipboard.writeText(MESSAGE_TEMPLATE);
      toast.success('Modèle copié ! Colle-le dans ton message.');
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
            // Aucun réseau renseigné dans le backoffice : on renvoie vers le téléphone et l'e-mail
            <h2 className="contact__title">Appelle le salon ou écris-moi par e-mail</h2>
          )}

          <div className="contact__checklist">
            <h3 className="contact__label">Dans ton message, indique-moi</h3>
            <ul>
              {CHECKLIST.map((item) => <li key={item}>{item}</li>)}
            </ul>
            <p className="muted">Pour un flash, donne-moi simplement son numéro (N°01, N°02…).</p>
            <button type="button" className="btn btn--ghost contact__copy" onClick={copyTemplate}>
              <FaRegCopy aria-hidden="true" /> Copier un modèle de message
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
          {salon?.email && (
            <div>
              <h2 className="contact__label">E-mail</h2>
              <p><a href={`mailto:${salon.email}`}>{salon.email}</a></p>
            </div>
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
          <iframe
            className="contact__map"
            title="Plan d'accès au salon"
            src={`https://maps.google.com/maps?q=${encodeURIComponent(fullAddress)}&output=embed`}
            loading="lazy"
          />
        </section>
      )}
    </>
  );
};

export default ContactPage;
