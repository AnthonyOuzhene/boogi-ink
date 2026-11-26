import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router';
import { FaChevronLeft, FaChevronRight, FaInstagram, FaPlay, FaTimes } from 'react-icons/fa';
import { assetUrl } from '@/api/client';
import useMediaQuery from '@/hooks/useMediaQuery';
import { formatDate, toParagraphs } from '@/utils/format';
import SketchCompare from './SketchCompare';
import Scribble from '@/components/ui/Scribble';
import './GalleryViewer.scss';

/**
 * ← → (ou glisser du doigt) : photo suivante, puis réalisation voisine arrivé au bout.
 * <dialog> + showModal() : le navigateur gère Échap, le fond assombri, et garde le focus à l'intérieur.
 */
const GalleryViewer = ({ gallery, position, total, onPrevious, onNext, onClose }) => {
  const dialogRef = useRef(null);
  const [photoIndex, setPhotoIndex] = useState(0);

  // "Réduire les animations" : le timelapse ne démarre pas tout seul
  const reduceMotion = useMediaQuery('(prefers-reduced-motion: reduce)');

  const slides = [
    { type: 'image', url: gallery.mainPictureUrl },
    ...(gallery.timelapseUrl ? [{ type: 'video', url: gallery.timelapseUrl }] : []),
    ...(gallery.pictures ?? []).map((picture) => ({ type: 'image', url: picture.url })),
  ];
  const slide = slides[photoIndex] ?? slides[0];
  const meta = [gallery.category?.name, gallery.realisedAt && formatDate(gallery.realisedAt)].filter(Boolean).join(' — ');
  const hasPrevious = position > 1;
  const hasNext = position < total;

  // Pas de dialog.close() au démontage : close() déclenche onClose, qui retire la réalisation de
  // l'URL, et <StrictMode> (dev) démonte/remonte : la visionneuse se refermait aussitôt ouverte.
  // "!dialog.open" évite une erreur au second montage.
  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog.open) dialog.showModal();
    document.documentElement.style.overflow = 'hidden';
    return () => {
      document.documentElement.style.overflow = '';
    };
  }, []);

  // Nouvelle réalisation : on repart de sa première photo
  useEffect(() => setPhotoIndex(0), [gallery.id]);

  const showPrevious = () => {
    if (photoIndex > 0) setPhotoIndex(photoIndex - 1);
    else if (hasPrevious) onPrevious();
  };

  const showNext = () => {
    if (photoIndex < slides.length - 1) setPhotoIndex(photoIndex + 1);
    else if (hasNext) onNext();
  };

  const handleKeyDown = (e) => {
    if (e.key === 'ArrowLeft') showPrevious();
    if (e.key === 'ArrowRight') showNext();
  };

  // Mobile : glisser vers la gauche = suivante, vers la droite = précédente
  const touchStartX = useRef(null);
  const handleTouchStart = (e) => { touchStartX.current = e.touches[0].clientX; };
  const handleTouchEnd = (e) => {
    const delta = e.changedTouches[0].clientX - touchStartX.current;
    if (Math.abs(delta) > 50) (delta < 0 ? showNext : showPrevious)();
  };

  // Clic sur le fond (hors photo et texte) : fermeture
  const handleClick = (e) => {
    if (e.target === e.currentTarget || e.target.classList.contains('viewer__stage')) dialogRef.current.close();
  };

  return (
    // onClose : déclenché par Échap ou par dialog.close() (bouton ×, clic sur le fond)
    <dialog
      ref={dialogRef}
      className="viewer"
      aria-labelledby="viewer-title"
      onClose={onClose}
      onKeyDown={handleKeyDown}
      onClick={handleClick}
    >
      <button type="button" className="viewer__close" aria-label="Fermer" onClick={() => dialogRef.current.close()}>
        <FaTimes size={22} aria-hidden="true" />
      </button>

      <div className="viewer__stage" onTouchStart={handleTouchStart} onTouchEnd={handleTouchEnd}>
        {photoIndex === 0 && gallery.sketchUrl ? (
          <SketchCompare
            key={gallery.id}
            photo={assetUrl(slides[0].url)}
            sketch={assetUrl(gallery.sketchUrl)}
            name={gallery.name}
          />
        ) : slide.type === 'video' ? (
          // muted : sinon le navigateur bloque la lecture automatique
          <video
            key={slide.url}
            src={assetUrl(slide.url)}
            poster={assetUrl(gallery.mainPictureUrl)}
            className="viewer__img viewer__video"
            aria-label={`${gallery.name} — timelapse de la séance`}
            controls
            muted
            loop
            playsInline
            autoPlay={!reduceMotion}
            preload="metadata"
          />
        ) : (
          <img
            key={slide.url}
            src={assetUrl(slide.url)}
            alt={`${gallery.name} — photo ${photoIndex + 1} sur ${slides.length}`}
            className="viewer__img"
          />
        )}

        {(photoIndex > 0 || hasPrevious) && (
          <button type="button" className="viewer__arrow viewer__arrow--prev" aria-label="Précédente" onClick={showPrevious}>
            <FaChevronLeft size={22} aria-hidden="true" />
          </button>
        )}
        {(photoIndex < slides.length - 1 || hasNext) && (
          <button type="button" className="viewer__arrow viewer__arrow--next" aria-label="Suivante" onClick={showNext}>
            <FaChevronRight size={22} aria-hidden="true" />
          </button>
        )}

        {slides.length > 1 && (
          <div className="viewer__thumbs">
            {slides.map((item, index) => (
              <button
                type="button"
                key={item.url}
                className="viewer__thumb"
                aria-label={item.type === 'video' ? 'Timelapse (vidéo)' : `Photo ${index + 1}`}
                aria-current={index === photoIndex}
                onClick={() => setPhotoIndex(index)}
              >
                <img src={assetUrl(item.type === 'video' ? gallery.mainPictureUrl : item.url)} alt="" />
                {item.type === 'video' && (
                  <span className="viewer__thumb-play" aria-hidden="true"><FaPlay size={14} /></span>
                )}
              </button>
            ))}
          </div>
        )}
      </div>

      <aside className="viewer__panel">
        {meta && <p className="eyebrow">{meta}</p>}
        <h2 id="viewer-title" className="viewer__title">{gallery.name}</h2>
        {gallery.isFavorite && <Scribble decorative={false} className="viewer__favorite">coup de cœur ♥</Scribble>}

        {gallery.sketchUrl && (
          <p className="viewer__hint">
            <span aria-hidden="true">↔</span> Du dessin à la peau : fais glisser le curseur sur la photo.
          </p>
        )}

        {toParagraphs(gallery.description).map((paragraph) => (
          <p key={paragraph} className="viewer__text">{paragraph}</p>
        ))}

        <div className="viewer__actions">
          {gallery.instagramUrl && (
            <a href={gallery.instagramUrl} target="_blank" rel="noreferrer" className="btn btn--ghost btn--instagram">
              <FaInstagram size={20} aria-hidden="true" /> Voir sur Instagram
            </a>
          )}
          <Link to="/contact" className="btn btn--primary">Un projet similaire ?</Link>
        </div>

        <p className="viewer__count muted">Réalisation {position} / {total}</p>
      </aside>
    </dialog>
  );
};

export default GalleryViewer;
