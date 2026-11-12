import { formatPrice } from '../utils/format';
import './ServiceList.scss';

/** Liste des prestations façon "carte" : nom ……… prix. withDetails affiche la description. */
const ServiceList = ({ services, withDetails = false }) => (
  <ul className="service-list">
    {services.map((service) => (
      <li key={service.id} className="service-list__item">
        <div className="service-list__row">
          <span className="service-list__name">{service.name}</span>
          <span className="service-list__dots" aria-hidden="true" />
          <span className={`service-list__price ${service.price ? 'text-cyan' : ''}`}>
            {formatPrice(service.price)}
          </span>
        </div>
        {withDetails && service.description && (
          <p className="service-list__description muted">{service.description}</p>
        )}
      </li>
    ))}
  </ul>
);

export default ServiceList;
