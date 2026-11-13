import { Navigate, useParams } from 'react-router';

/**
 * Ancienne adresse d'une réalisation (/realisations/12) : elle s'ouvre désormais dans la
 * visionneuse de la page Réalisations. On redirige pour que les liens déjà partagés marchent.
 * "replace" : l'ancienne adresse ne reste pas dans l'historique du navigateur.
 */
const GalleryDetailPage = () => {
  const { id } = useParams();

  return <Navigate to={`/realisations?realisation=${id}`} replace />;
};

export default GalleryDetailPage;
