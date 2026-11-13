import { useState } from 'react';
import { Link } from 'react-router';
import { useSalon } from '../context/SalonContext';
import { toLines } from '../utils/format';
import PageHero from '../components/PageHero';
import SocialLinks from '../components/SocialLinks';
import './pages.scss';

const ContactPage = () => {
  // Coordonnées, horaires et réseaux : backoffice, menu "Salon"
  const { salon } = useSalon();
  const fullAddress = salon ? `${salon.address}, ${salon.zipCode} ${salon.city}` : null;

  // Pas encore d'envoi d'email côté Symfony : le formulaire ouvre la messagerie
  // du visiteur avec le message pré-rempli (lien "mailto:")
  const [form, setForm] = useState({ name: '', subject: '', message: '' });
  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });
  const handleSubmit = (e) => {
    e.preventDefault();
    const body = `${form.message}\n\n${form.name}`;
    window.location.href = `mailto:${salon?.email ?? ''}?subject=${encodeURIComponent(form.subject)}&body=${encodeURIComponent(body)}`;
  };

  return (
    <>
      <PageHero
        eyebrow="Prendre rendez-vous"
        title={<>Par<span className="text-cyan">lons-en</span></>}
        lead="Décrivez votre projet : style, emplacement, taille, références. Nous revenons vers vous pour en discuter."
      />

      <section className="container section section--tight contact">
        <div className="contact__info">
          {salon && (
            <>
              <div>
                <h2 className="contact__label">Adresse</h2>
                <p>{salon.address}<br />{salon.zipCode} {salon.city}</p>
                {!salon.isOpen && <p className="field__error">Le salon est actuellement fermé.</p>}
              </div>
              <div>
                <h2 className="contact__label">Téléphone</h2>
                <p><a href={`tel:${salon.phoneNumber.replace(/\s/g, '')}`}>{salon.phoneNumber}</a></p>
              </div>
            </>
          )}
          {salon?.email && (
            <div>
              <h2 className="contact__label">E-mail</h2>
              <p><a href={`mailto:${salon.email}`}>{salon.email}</a></p>
            </div>
          )}
          {salon?.openingHours && (
            <div>
              <h2 className="contact__label">Horaires</h2>
              <p className="contact__hours">
                {toLines(salon.openingHours).map((line) => <span key={line}>{line}</span>)}
              </p>
            </div>
          )}
          {/* La FAQ n'est plus dans le menu : on la rappelle ici, là où l'on se pose des questions */}
          <div>
            <h2 className="contact__label">Une question ?</h2>
            <p>La réponse est peut-être dans la <Link to="/faq">FAQ</Link>.</p>
          </div>
          {(salon?.instagramUrl || salon?.facebookUrl) && (
            <div>
              <h2 className="contact__label">Suivez-nous</h2>
              <SocialLinks />
            </div>
          )}
        </div>

        <form className="form neon-card contact__form" onSubmit={handleSubmit}>
          <div className="field">
            <label htmlFor="name">Nom et prénom</label>
            <input id="name" name="name" type="text" required autoComplete="name" value={form.name} onChange={handleChange} />
          </div>
          <div className="field">
            <label htmlFor="subject">Sujet</label>
            <input id="subject" name="subject" type="text" required placeholder="Ex. : projet de tatouage réaliste"
              value={form.subject} onChange={handleChange} />
          </div>
          <div className="field">
            <label htmlFor="message">Votre projet</label>
            <textarea id="message" name="message" required value={form.message} onChange={handleChange} />
          </div>
          <div>
            <button type="submit" className="btn btn--primary">Envoyer</button>
          </div>
        </form>
      </section>

      {fullAddress && (
        <section className="container section">
          <iframe
            className="contact__map"
            title="Plan d'accès au salon"
            src={`https://maps.google.com/maps?q=${encodeURIComponent(fullAddress)}&output=embed`}
            loading="lazy"
          />
        </section>
      )}
    </>
  );
};

export default ContactPage;
