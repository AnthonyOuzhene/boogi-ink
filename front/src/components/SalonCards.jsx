import { GiAnchor, GiQuillInk, GiSewingNeedle, GiSwallow } from 'react-icons/gi';
import { useSalon } from '../context/SalonContext';
import './SalonCards.scss';

/**
 * Les points forts du salon, en "cartes à jouer" posées en éventail sous la grande photo
 * de l'accueil. Les symboles des coins remplacent pique / cœur / carreau / trèfle par des
 * motifs du tatouage traditionnel (plume et encre, hirondelle, aiguille, ancre).
 * Textes fixes ; l'adresse et la ville viennent du backoffice (menu "Salon").
 */
const SalonCards = () => {
  const { salon } = useSalon();

  const cards = [
    {
      Icon: GiQuillInk,
      title: 'Dessins sur mesure',
      text: 'Chaque tatouage est dessiné pour vous, à partir de votre idée. Pas de copie, pas de modèle tout fait.',
    },
    {
      Icon: GiSwallow,
      title: 'Sur rendez-vous',
      text: "On prend le temps d'échanger sur votre projet avant de tatouer, au calme et sans attente.",
    },
    {
      Icon: GiSewingNeedle,
      title: 'Hygiène irréprochable',
      text: 'Aiguilles stériles à usage unique, poste désinfecté à chaque séance : votre peau est entre de bonnes mains.',
    },
    {
      Icon: GiAnchor,
      title: salon?.city ? `À ${salon.city}` : 'Le salon',
      text: salon?.address
        ? `Un salon chaleureux, ${salon.address}. Passez nous voir ou écrivez-nous.`
        : 'Un salon chaleureux où vous êtes accueilli comme à la maison.',
    },
  ];

  return (
    <section aria-label="Les points forts du salon" className="salon-cards">
      <ul className="salon-cards__hand">
        {cards.map(({ Icon, title, text }) => (
          // .paper : fond papier et couleurs "encre" (voir global.scss)
          <li key={title} className="playing-card paper">
            <Icon className="playing-card__corner" aria-hidden="true" />
            <Icon className="playing-card__corner playing-card__corner--bottom" aria-hidden="true" />
            <h2 className="playing-card__title">{title}</h2>
            <p className="playing-card__text">{text}</p>
          </li>
        ))}
      </ul>
    </section>
  );
};

export default SalonCards;
