import { FaFacebookF, FaInstagram } from 'react-icons/fa';
import { useSalon } from '@/context/SalonContext';
import './SocialLinks.scss';

/**
 * Logos Instagram / Facebook du salon (adresses saisies dans le backoffice, menu "Salon").
 * Icônes seules : le nom du réseau est lu par les lecteurs d'écran (aria-label)
 * et s'affiche en infobulle au survol (title).
 * N'affiche rien si aucun réseau n'est renseigné.
 */
const NETWORKS = [
  { key: 'instagramUrl', label: 'Instagram', Icon: FaInstagram, modifier: 'instagram' },
  { key: 'facebookUrl', label: 'Facebook', Icon: FaFacebookF, modifier: 'facebook' },
];

const SocialLinks = ({ className = '' }) => {
  const { salon } = useSalon();
  const links = NETWORKS.filter((network) => salon?.[network.key]);

  if (links.length === 0) return null;

  return (
    <ul className={`social-links ${className}`}>
      {links.map(({ key, label, Icon, modifier }) => (
        <li key={key}>
          <a
            href={salon[key]}
            target="_blank"
            rel="noreferrer"
            className={`social-links__link social-links__link--${modifier}`}
            aria-label={`${label} (nouvel onglet)`}
            title={label}
          >
            <Icon size={20} aria-hidden="true" />
          </a>
        </li>
      ))}
    </ul>
  );
};

export default SocialLinks;
