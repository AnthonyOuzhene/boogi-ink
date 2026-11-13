import { useMemo } from 'react';
import useApi from '../hooks/useApi';
import { SalonContext } from './SalonContext';

/**
 * Charge la fiche du salon UNE fois pour tout le site (au lieu d'un appel par composant)
 * et la rend accessible partout via useSalon().
 */
export function SalonProvider({ children }) {
  const { data: homes, loading } = useApi('/homes');
  const value = useMemo(() => ({ salon: homes?.[0] ?? null, loading }), [homes, loading]);

  return <SalonContext value={value}>{children}</SalonContext>;
}
