import { useState } from 'react';
import useApi from '../hooks/useApi';
import { CONTACT_EMAIL, FACEBOOK_URL, INSTAGRAM_URL } from '../config';
import PageHero from '../components/PageHero';
import './pages.scss';

const ContactPage = () => {
  const { data: homes } = useApi('/homes');
  const salon = homes?.[0];
  const fullAddress = salon ? `${salon.address}, ${salon.zipCode} ${salon.city}` : null;

  // Pas encore d'envoi d'email côté Symfony : le formulaire ouvre la messagerie
  // du visiteur avec le message pré-rempli (lien "mailto:")
  const [form, setForm] = useState({ name: '', subject: '', message: '' });
  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });
  const handleSubmit = (e) => {
    e.preventDefault();
    const body = `${form.message}\n\n${form.name}`;
    window.location.href = `mailto:${CONTACT_EMAIL}?subject=${encodeURIComponent(form.subject)}&body=${encodeURIComponent(body)}`;
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
          <div>
            <h2 className="contact__label">E-mail</h2>
            <p><a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a></p>
          </div>
          <div>
            <h2 className="contact__label">Suivez-nous</h2>
            <p className="contact__social">
              <a href={INSTAGRAM_URL} target="_blank" rel="noreferrer">Instagram</a>
              <a href={FACEBOOK_URL} target="_blank" rel="noreferrer">Facebook</a>
            </p>
          </div>
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
