import { useSalon } from '../context/SalonContext';
import './ArgumentsBand.scss';

/**
 * Bandeau sous la grande photo de l'accueil : les points forts du salon en quelques mots,
 * pour rassurer un visiteur qui le découvre. Texte fixe (pas de lien), sauf la ville du salon
 * qui vient du backoffice (menu "Salon").
 */
const ArgumentsBand = () => {
  const { salon } = useSalon();

  const items = [
    'Dessins sur mesure',
    'Sur rendez-vous',
    'Hygiène irréprochable',
    salon?.city,
  ].filter(Boolean);

  return (
    <section aria-label="Les points forts du salon" className="arguments-band">
      <div className="arguments-band__scroller">
        <ul className="arguments-band__list">
          {/* Les "/" entre les éléments sont ajoutés en CSS (li + li::before) */}
          {items.map((item) => <li key={item}>{item}</li>)}
        </ul>
      </div>
    </section>
  );
};

export default ArgumentsBand;
