import { useRef, useState } from 'react';
import { FaArrowLeft, FaArrowRight } from 'react-icons/fa';
import useApi from '../hooks/useApi';
import useMediaQuery from '../hooks/useMediaQuery';
import { formatDate } from '../utils/format';
import PageHero from '../components/PageHero';
import CommentForm from '../components/CommentForm';
import './pages.scss';

// Couleurs des punaises, en alternance (voir .pin dans global.scss)
const PIN_COLORS = ['', 'pin--pink', 'pin--blue', 'pin--yellow'];

// Avis par page : 3 colonnes × 2 lignes sur grand écran ; sur mobile et tablette
// (1 ou 2 colonnes) on en montre moins pour ne pas faire défiler trop loin
const PER_PAGE_DESKTOP = 6;
const PER_PAGE_MOBILE = 4;
const MOBILE_QUERY = '(max-width: 1024px)'; // couvre les tablettes, y compris en paysage

// Respecte le réglage système "réduire les animations" : remontée instantanée, sans glissement
const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const VisitorBookPage = () => {
  // L'API ne renvoie que les avis validés dans le backoffice, du plus récent au plus ancien
  const { data: comments, loading, error } = useApi('/comments');
  const isMobile = useMediaQuery(MOBILE_QUERY);
  const perPage = isMobile ? PER_PAGE_MOBILE : PER_PAGE_DESKTOP;

  // On retient le PREMIER AVIS affiché plutôt qu'un numéro de page : si l'écran passe
  // de 6 à 4 avis par page (ou l'inverse), on reste sur la page qui contient cet avis
  const [firstIndex, setFirstIndex] = useState(0);
  const listRef = useRef(null);

  const count = comments?.length ?? 0;
  const pageCount = Math.ceil(count / perPage);
  const page = Math.min(Math.floor(firstIndex / perPage), Math.max(pageCount - 1, 0));
  const start = page * perPage;
  const visibleComments = comments?.slice(start, start + perPage) ?? [];

  const goToPage = (newPage) => {
    setFirstIndex(newPage * perPage);
    // Les boutons sont sous la grille : on remonte au début des avis de la nouvelle page
    // (scroll-margin-top dans le SCSS évite qu'ils passent sous le header fixe)
    listRef.current?.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
  };

  return (
    <>
      <PageHero
        eyebrow="Ils m'ont fait confiance"
        title={<>Livre <span className="text-cyan">d'or</span></>}
      />

      {/* Les avis sur papier ; le formulaire reste sur fond sombre en dessous */}
      <section className="band band--flush paper">
        <div className="container">
          {loading && <p className="muted">Chargement…</p>}
          {error && <p className="muted">Impossible de charger les avis.</p>}
          {comments?.length === 0 && <p className="muted">Aucun avis pour le moment. Sois le premier !</p>}

          {/* key={page} : la grille est recréée à chaque page, ce qui rejoue l'animation d'apparition */}
          <div className="comments" key={page} ref={listRef}>
            {visibleComments.map((comment, index) => (
              <article key={comment.id} className="comment">
                {/* Punaise décorative, couleur en alternance (sur la position dans TOUTE la liste) */}
                <span className={`pin ${PIN_COLORS[(start + index) % PIN_COLORS.length]}`} aria-hidden="true" />
                <span className="comment__mark" aria-hidden="true">“</span>
                <h2 className="comment__title">{comment.title}</h2>
                <p className="comment__message">{comment.message}</p>
                <p className="comment__meta">
                  <span>{comment.authorName}</span>
                  {comment.projectName && <span className="text-cyan">{comment.projectName}</span>}
                  <span className="muted">{formatDate(comment.createdAt)}</span>
                </p>
              </article>
            ))}
          </div>

          {pageCount > 1 && (
            <nav className="pagination" aria-label="Pages des avis">
              <button
                type="button"
                className="pagination__arrow"
                aria-label="Page précédente"
                disabled={page === 0}
                onClick={() => goToPage(page - 1)}
              >
                <FaArrowLeft aria-hidden="true" />
              </button>
              <ol className="pagination__pages">
                {Array.from({ length: pageCount }, (_, index) => (
                  <li key={index}>
                    <button
                      type="button"
                      className="pagination__page"
                      aria-label={`Page ${index + 1} sur ${pageCount}`}
                      aria-current={index === page ? 'page' : undefined}
                      onClick={() => goToPage(index)}
                    >
                      {index + 1}
                    </button>
                  </li>
                ))}
              </ol>
              <button
                type="button"
                className="pagination__arrow"
                aria-label="Page suivante"
                disabled={page === pageCount - 1}
                onClick={() => goToPage(page + 1)}
              >
                <FaArrowRight aria-hidden="true" />
              </button>
            </nav>
          )}
        </div>
      </section>

      <section className="container section">
        <CommentForm />
      </section>
    </>
  );
};

export default VisitorBookPage;
