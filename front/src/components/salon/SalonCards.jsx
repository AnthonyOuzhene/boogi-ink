import { GiAnchor, GiCompass, GiDrop, GiHeartInside, GiQuillInk, GiRose, GiSewingNeedle, GiSwallow } from 'react-icons/gi';
import useApi from '@/hooks/useApi';
import './SalonCards.scss';

/** Clés = HomeCard::ICONS (back/src/Entity/HomeCard.php), à garder synchronisées. */
const ICONS = {
  quill: GiQuillInk,
  swallow: GiSwallow,
  needle: GiSewingNeedle,
  anchor: GiAnchor,
  rose: GiRose,
  heart: GiHeartInside,
  compass: GiCompass,
  drop: GiDrop,
};

/** Contenu : backoffice, menu "Cartes de l'accueil". */
const SalonCards = () => {
  const { data: cards } = useApi('/home_cards');
  if (!cards?.length) return null;

  return (
    <section aria-label="Les points forts du salon" className="salon-cards">
      <ul className="salon-cards__hand">
        {cards.map(({ id, icon, title, text }) => {
          const Icon = ICONS[icon] ?? GiQuillInk; // symbole inconnu : la plume par défaut
          return (
            // .paper : fond papier et couleurs "encre" (voir styles/_paper.scss)
            <li key={id} className="playing-card paper">
              <Icon className="playing-card__corner" aria-hidden="true" />
              <Icon className="playing-card__corner playing-card__corner--bottom" aria-hidden="true" />
              <h2 className="playing-card__title">{title}</h2>
              <p className="playing-card__text">{text}</p>
            </li>
          );
        })}
      </ul>
    </section>
  );
};

export default SalonCards;
