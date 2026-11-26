import { Navigate, useParams } from 'react-router';

// Ancienne adresse /realisations/12 : redirige vers la visionneuse pour les liens déjà partagés
const GalleryDetailPage = () => {
  const { id } = useParams();

  return <Navigate to={`/realisations?realisation=${id}`} replace />;
};

export default GalleryDetailPage;
