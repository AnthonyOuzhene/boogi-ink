import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import useScrollToHash from '../hooks/useScrollToHash';
import usePageTitle from '../hooks/usePageTitle';
import { useSalon } from '../context/SalonContext';
import { toParagraphs } from '../utils/format';
import PageHero from '../components/PageHero';
import ArtistSection from '../components/ArtistSection';
import SessionSteps from '../components/SessionSteps';
import FlashGallery from '../components/FlashGallery';
import Timeline from '../components/atelier/Timeline';
import StudioGallery from '../components/atelier/StudioGallery';
import HygieneCards from '../components/atelier/HygieneCards';
import './pages.scss';

/**
 * L'atelier : le récit complet du salon. L'accueil n'en montre que des aperçus
 * qui renvoient ici (/atelier#artiste, #seance).
 * Tout le contenu vient du backoffice ; une section sans contenu n'est pas affichée.
 */
const AtelierPage = () => {
  usePageTitle("L'atelier");
  const { salon } = useSalon();
  const { data: milestones, loading: milestonesLoading } = useApi('/milestones');
  const { data: artists, loading: artistsLoading } = useApi('/artists');
  const { data: photos, loading: photosLoading } = useApi('/studio_photos');
  const { data: steps, loading: stepsLoading } = useApi('/session_steps');
  const { data: commitments, loading: commitmentsLoading } = useApi('/hygiene_commitments');
  const { data: flashes, loading: flashesLoading } = useApi('/flashes');

  // Une seule requête pour les deux frises, triées ensuite côté navigateur
  const salonMilestones = milestones?.filter((milestone) => milestone.timeline === 'salon') ?? [];
  const artistMilestones = milestones?.filter((milestone) => milestone.timeline === 'artist') ?? [];
  const artist = artists?.[0];

  // Sections affichées, dans l'ordre : elles alimentent le sommaire et la numérotation 01, 02…
  const sections = [
    (salonMilestones.length > 0 || salon?.storyIntro) && { id: 'histoire', label: "L'histoire" },
    artist && { id: 'artiste', label: "L'artiste" },
    photos?.length > 0 && { id: 'lieu', label: 'Le lieu' },
    steps?.length > 0 && { id: 'seance', label: 'Une séance' },
    commitments?.length > 0 && { id: 'hygiene', label: 'Hygiène' },
    flashes?.length > 0 && { id: 'flashs', label: 'Flashs' },
  ].filter(Boolean);
  const has = (id) => sections.some((section) => section.id === id);
  const number = (id) => String(sections.findIndex((section) => section.id === id) + 1).padStart(2, '0');

  // Toutes les données sont là : on peut défiler jusqu'à la section demandée dans l'URL
  useScrollToHash(![milestonesLoading, artistsLoading, photosLoading, stepsLoading, commitmentsLoading, flashesLoading].includes(true));

  // "Depuis 2012" si la première date du salon est une année, sinon un surtitre neutre
  const since = salonMilestones[0]?.year;
  const historyEyebrow = /^\d{4}$/.test(since ?? '') ? `Depuis ${since}` : 'Le salon';

  return (
    <>
      <PageHero
        eyebrow="Le salon"
        title={<>L'ate<span className="text-cyan">lier</span></>}
        lead="L'histoire de Boogi'ink, l'artiste qui le fait vivre, le lieu, et tout ce qu'il faut savoir avant de passer sous l'aiguille."
      >
        {sections.length > 0 && (
          <nav aria-label="Sommaire de la page" className="atelier-toc">
            <ul className="chips">
              {sections.map((section) => (
                <li key={section.id}>
                  <a href={`#${section.id}`} className="chip">{section.label}</a>
                </li>
              ))}
            </ul>
          </nav>
        )}
      </PageHero>

      {/* --- L'histoire du salon --- */}
      {has('histoire') && (
        <section id="histoire" className="container section split anchor">
          <div className="split__intro">
            <p className="eyebrow eyebrow--pink">{number('histoire')} — {historyEyebrow}</p>
            <h2 className="section-title section-title--small">L'his<span className="text-cyan">toire</span></h2>
            {toParagraphs(salon?.storyIntro).map((paragraph) => (
              <p key={paragraph} className="muted lead-text">{paragraph}</p>
            ))}
          </div>
          <div className="split__list">
            <Timeline milestones={salonMilestones} />
          </div>
        </section>
      )}

      {/* --- Le parcours de l'artiste : biographie complète + frise --- */}
      {has('artiste') && (
        <div className="section">
          <ArtistSection id="artiste" variant="full" artist={artist} eyebrow={`${number('artiste')} — L'artiste`}>
            {artistMilestones.length > 0 && <Timeline milestones={artistMilestones} direction="horizontal" />}
          </ArtistSection>
        </div>
      )}

      {/* --- Le lieu en images --- */}
      {has('lieu') && (
        <section id="lieu" className="container section anchor">
          <div className="section-head">
            <div className="stack">
              <p className="eyebrow eyebrow--pink">{number('lieu')} — Le lieu</p>
              <h2 className="section-title">Bienvenue <span className="text-cyan">au salon</span></h2>
            </div>
            <p className="muted section-head__aside">
              {salon?.address && `${salon.address}, ${salon.city}. `}
              {salon?.placeDescription}
            </p>
          </div>
          <StudioGallery photos={photos} />
        </section>
      )}

      {/* --- Le déroulé d'une séance --- */}
      {has('seance') && (
        <section id="seance" className="band paper anchor">
          <div className="container split">
            <div className="split__intro">
              <p className="eyebrow eyebrow--pink">{number('seance')} — Pas à pas</p>
              <h2 className="section-title section-title--small">Une séance <span className="text-cyan">au salon</span></h2>
              <p className="muted lead-text">De la première idée à la cicatrisation complète : voici comment se passe un tatouage chez Boogi'ink.</p>
              <Link to="/contact" className="btn btn--primary">Commencer mon projet</Link>
              <p className="muted">Une question avant de te lancer ? <Link to="/faq">Consulte la FAQ</Link></p>
            </div>
            <div className="split__list">
              <SessionSteps steps={steps} />
            </div>
          </div>
        </section>
      )}

      {/* --- Hygiène & sécurité : bandeau sur fond clair --- */}
      {has('hygiene') && (
        <section id="hygiene" className="band anchor">
          <div className="container">
            <div className="band__head">
              <p className="eyebrow eyebrow--pink">{number('hygiene')} — En toute confiance</p>
              <h2 className="section-title section-title--small">Hygiène <span className="text-cyan">&amp; sécurité</span></h2>
            </div>
            <HygieneCards commitments={commitments} />
            <p className="band__more">
              <Link to="/pages/regles-sanitaires" className="link-arrow">Lire les règles sanitaires →</Link>
            </p>
          </div>
        </section>
      )}

      {/* --- Les flashs --- */}
      {has('flashs') && (
        <section id="flashs" className="band paper anchor">
          <div className="container">
            <div className="section-head">
              <div className="stack">
                <p className="eyebrow eyebrow--pink">{number('flashs')} — Prêts à tatouer</p>
                <h2 className="section-title">Les <span className="glow">flashs</span></h2>
                <p className="muted lead-text">
                  Des motifs dessinés par {artist?.firstName ?? "l'artiste"}, prêts à être tatoués.
                  Chaque flash n'est tatoué qu'une seule fois : une fois réservé, il est à toi.
                </p>
              </div>
            </div>
            <FlashGallery flashes={flashes} />
          </div>
        </section>
      )}

      {/* --- Appel final --- */}
      <section className="container section">
        <div className="cta-band">
          <h2 className="section-title">Un projet ?<br /><span className="glow">Parlons-en.</span></h2>
          <div className="cta-band__actions">
            <Link to="/contact" className="btn btn--primary">Prendre rendez-vous</Link>
            <Link to="/realisations" className="btn btn--ghost">Voir les réalisations</Link>
          </div>
        </div>
      </section>
    </>
  );
};

export default AtelierPage;
