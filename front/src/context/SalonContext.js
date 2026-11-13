import { createContext, useContext } from 'react';

/**
 * Informations du salon (coordonnées, horaires, réseaux, accroche de l'accueil),
 * gérées dans le backoffice (menu "Salon") et chargées une seule fois par <SalonProvider>.
 */
export const SalonContext = createContext({ salon: null, loading: true });

/** const { salon, loading } = useSalon(); — salon vaut null tant que rien n'est chargé */
export const useSalon = () => useContext(SalonContext);
