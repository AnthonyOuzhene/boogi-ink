import {
  FaBatteryFull, FaHome, FaInstagram, FaPlay, FaRegClone, FaRegHeart, FaRegPlusSquare,
  FaSearch, FaSignal, FaTh, FaUserCircle, FaWifi,
} from 'react-icons/fa';
import useApi from '@/hooks/useApi';
import useMediaQuery from '@/hooks/useMediaQuery';
import { useSalon } from '@/context/SalonContext';
import { assetUrl } from '@/api/client';
import Scribble from '@/components/ui/Scribble';
import logo from '@/assets/images/logo.webp';
import './InstagramFeed.scss';

/** "https://www.instagram.com/boogi_ink/" → "boogi_ink" */
const handleFromUrl = (url) => url?.match(/instagram\.com\/([^/?#]+)/)?.[1] ?? null;

/**
 * Bande "Sur Instagram" de l'accueil : un téléphone (dessiné en CSS) affiche le profil
 * du salon et ses 6 derniers posts ; à côté (ou au-dessus sur petit écran), le texte et le bouton "Suivre".
 *
 * Les posts sont récupérés par le back (commande app:instagram:sync, toutes les 3 heures)
 * et les images sont servies par notre serveur : le visiteur ne contacte jamais Instagram
 * (pas de cookie, pas de bandeau de consentement). Au clic, le post s'ouvre sur Instagram.
 * Aucun post (pas encore connecté, panne…) : la bande ne s'affiche pas.
 */
const InstagramFeed = () => {
  const { data: posts } = useApi('/instagram_posts');
  const { salon } = useSalon();
  // Jusqu'à 1 100 px : téléphone sous le texte (même seuil que InstagramFeed.scss) ;
  // sert à orienter la flèche de l'annotation vers le téléphone
  const isStacked = useMediaQuery('(max-width: 1100px)');

  if (!posts?.length) return null;

  const handle = handleFromUrl(salon?.instagramUrl);
  const profileUrl = salon?.instagramUrl ?? posts[0].permalink;

  return (
    <section className="instagram-feed" aria-labelledby="instagram-title">
      <div className="container instagram-feed__inner">
        {/* --- Texte --- */}
        <div className="instagram-feed__text">
          <p className="eyebrow">Les dernières nouvelles de l'atelier</p>
          <h2 id="instagram-title" className="section-title">Sur <span className="text-cyan">Instagram</span></h2>
          <p className="muted lead-text">
            Tatouages du jour, flashs disponibles, coulisses de l'atelier : c'est là que tout se passe en premier.
          </p>
          <a href={profileUrl} target="_blank" rel="noreferrer" className="btn btn--ghost btn--instagram instagram-feed__follow">
            <FaInstagram size={20} aria-hidden="true" /> Suivre {handle ? `@${handle}` : 'sur Instagram'}
          </a>
        </div>

        {/* --- Téléphone : profil + grille des 6 derniers posts ---
            L'annotation au feutre est accrochée au téléphone : juste à sa gauche sur grand écran
            (flèche vers lui), au-dessus quand il passe sous le texte (flèche vers le bas) */}
        <div className="instagram-feed__device">
        <Scribble arrow={isStacked ? 'down' : 'right'} className="instagram-feed__scribble">fraîchement encré !</Scribble>
        <div className="device">
          <div className="device__screen">
            {/* Barre d'état (décorative) */}
            <div className="device__status" aria-hidden="true">
              <span>9:41</span>
              <span className="device__notch" />
              <span className="device__status-icons"><FaSignal /><FaWifi /><FaBatteryFull /></span>
            </div>

            <div className="device__body">
              {/* Barre de navigation (décorative), affichée sous la grille */}
              <div className="device__nav" aria-hidden="true">
                <FaHome /><FaSearch /><FaRegPlusSquare /><FaRegHeart /><FaUserCircle />
              </div>

              <div className="device__main">
                {/* En-tête du profil */}
                <div className="device__profile">
                  <img src={logo} alt="" className="device__avatar" />
                  <div className="device__identity">
                    <strong>{handle ?? "Boogi'ink"}</strong>
                    <span>Salon de tatouage{salon?.city && ` · ${salon.city}`}</span>
                  </div>
                  <a href={profileUrl} target="_blank" rel="noreferrer" className="device__follow" tabIndex={-1} aria-hidden="true">
                    Suivre
                  </a>
                </div>

                <div className="device__tabs" aria-hidden="true"><FaTh /></div>

                {/* Les 6 posts, en grille 3 × 2 comme sur Instagram */}
                <ul className="device__grid">
                  {posts.map((post) => (
                    <li key={post.id}>
                      <a
                        href={post.permalink}
                        target="_blank"
                        rel="noreferrer"
                        className="device__post"
                        title={post.caption ?? undefined}
                        aria-label={`Voir sur Instagram : ${post.caption ? post.caption.slice(0, 80) : 'post du salon'}`}
                      >
                        <img src={assetUrl(post.imageUrl)} alt="" loading="lazy" />
                        {/* Reel (▶) ou plusieurs photos : petit symbole en haut à droite, comme sur Instagram */}
                        {post.mediaType === 'VIDEO' && <FaPlay className="device__type" aria-hidden="true" />}
                        {post.mediaType === 'CAROUSEL_ALBUM' && <FaRegClone className="device__type" aria-hidden="true" />}
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </section>
  );
};

export default InstagramFeed;
