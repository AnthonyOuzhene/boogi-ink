import { Fragment } from 'react';
import { Link } from 'react-router';
import useApi from '../hooks/useApi';
import './CategoryBand.scss';

/** Bandeau des styles de tatouage ; chaque style mène à la galerie filtrée */
const CategoryBand = () => {
  const { data: categories } = useApi('/categories');
  if (!categories?.length) return null;

  return (
    <nav aria-label="Styles de tatouage" className="category-band">
      {categories.map((category, index) => (
        <Fragment key={category.id}>
          {index > 0 && <span className="category-band__sep" aria-hidden="true">/</span>}
          <Link to={`/realisations?category=${category.id}`}>{category.name}</Link>
        </Fragment>
      ))}
    </nav>
  );
};

export default CategoryBand;
