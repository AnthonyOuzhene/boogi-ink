import { createContext, useContext } from 'react';

/** Fiche du salon (backoffice, menu "Salon"), chargée une seule fois par <SalonProvider> */
export const SalonContext = createContext({ salon: null, loading: true });

/** const { salon, loading } = useSalon(); — salon vaut null tant que rien n'est chargé */
export const useSalon = () => useContext(SalonContext);
