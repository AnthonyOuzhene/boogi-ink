import { useMemo } from 'react';
import useApi from '@/hooks/useApi';
import { SalonContext } from './SalonContext';

/** Un seul appel pour tout le site au lieu d'un appel par composant ; lecture via useSalon() */
export function SalonProvider({ children }) {
  const { data: homes, loading } = useApi('/homes');
  const value = useMemo(() => ({ salon: homes?.[0] ?? null, loading }), [homes, loading]);

  return <SalonContext value={value}>{children}</SalonContext>;
}
