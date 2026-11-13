import { useState } from 'react';
import './SketchCompare.scss';

/**
 * "Du dessin à la peau" : le dessin préparatoire posé par-dessus la photo du tatouage,
 * découpé selon la position du curseur (à gauche le dessin, à droite le tatouage).
 *
 * Le curseur est un vrai <input type="range"> rendu invisible et étendu sur toute l'image :
 * utilisable à la souris, au doigt, au clavier (flèches) et reconnu par les lecteurs d'écran.
 */
const SketchCompare = ({ photo, sketch, name }) => {
  const [position, setPosition] = useState(50); // % de dessin visible, depuis la gauche

  // Dans la visionneuse, glisser du doigt et les flèches changent de photo :
  // sur le curseur, ces gestes doivent seulement le déplacer
  const keepForSlider = (e) => e.stopPropagation();

  return (
    <div
      className="compare"
      style={{ '--position': `${position}%` }}
      onTouchStart={keepForSlider}
      onTouchEnd={keepForSlider}
    >
      <img src={photo} alt={`${name} — le tatouage`} className="compare__photo viewer__img" />
      <img src={sketch} alt={`${name} — le dessin préparatoire`} className="compare__sketch" />

      <span className="compare__handle" aria-hidden="true" />
      <span className="compare__label compare__label--sketch" aria-hidden="true">Dessin</span>
      <span className="compare__label compare__label--photo" aria-hidden="true">Tatouage</span>

      <input
        type="range"
        min="0"
        max="100"
        value={position}
        onChange={(e) => setPosition(Number(e.target.value))}
        onKeyDown={keepForSlider}
        className="compare__range"
        aria-label="Du dessin au tatouage : faire glisser pour comparer"
        aria-valuetext={`${position} % de dessin`}
      />
    </div>
  );
};

export default SketchCompare;
