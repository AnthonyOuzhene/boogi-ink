/** En-tête des pages intérieures : surtitre cyan, grand titre, chapeau facultatif */
const PageHero = ({ eyebrow, title, lead, children }) => (
  <section className="container">
    <div className="page-hero">
      {eyebrow && <p className="eyebrow">{eyebrow}</p>}
      <h1 className="page-hero__title">{title}</h1>
      {lead && <p className="page-hero__lead">{lead}</p>}
      {children}
    </div>
  </section>
);

export default PageHero;
