import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { searchProviders } from '../api/bookings';
import { fetchCategories } from '../api/providers';
import './Search.css';
import { useAuth } from '../context/AuthContext';
import { fetchFavorites, toggleFavorite as apiToggleFavorite } from '../api/favorites';
import { motion } from 'framer-motion';

const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const initialFilters = {
  category_id: '', location: '', price_min: '', price_max: '',
  keyword: '', sort: 'rating',
};

export default function Search() {
  const [categories, setCategories] = useState([]);
  const [filters, setFilters] = useState(initialFilters);
  const [results, setResults] = useState(null);
  const [loading, setLoading] = useState(false);
  const { user } = useAuth();
const [favorites, setFavorites] = useState([]);

useEffect(() => {
  if (user?.role === 'client') {
    fetchFavorites()
      .then((data) => setFavorites(data || []))
      .catch(() => setFavorites([]));
  }
}, [user]);

const isFavorited = (id) =>
  favorites.some((f) => f.provider_profile_id === id);

async function toggleFavorite(e, id) {
  e.preventDefault(); // prevents clicking the heart from navigating to the provider profile
  if (user?.role !== 'client') {
    alert('Log in as a client to save favorites.');
    return;
  }
  
  // Optimistic UI update (feels instant)
  const alreadyFav = isFavorited(id);
  if (alreadyFav) {
    setFavorites((prev) => prev.filter((f) => f.provider_profile_id !== id));
  } else {
    setFavorites((prev) => [...prev, { provider_profile_id: id }]);
  }

  try {
    await apiToggleFavorite(id);
  } catch {
    // Revert if the API call fails
    if (alreadyFav) {
      setFavorites((prev) => [...prev, { provider_profile_id: id }]);
    } else {
      setFavorites((prev) => prev.filter((f) => f.provider_profile_id !== id));
    }
  }
}

  useEffect(() => { fetchCategories().then(setCategories); }, []);

  useEffect(() => {
    setLoading(true);
    const t = setTimeout(() => {
      const clean = Object.fromEntries(
        Object.entries(filters).filter(([, v]) => v !== '')
      );
      searchProviders(clean).then(setResults).finally(() => setLoading(false));
    }, 300);
    return () => clearTimeout(t);
  }, [filters]);

  function updateFilter(e) {
    const { name, value } = e.target;
    setFilters((p) => ({ ...p, [name]: value }));
  }

  function goToPage(url) {
    if (!url) return;
    setLoading(true);
    const rel = url.replace(/^https?:\/\/[^/]+/, '');
    import('../api/axios').then(({ default: api }) =>
      api.get(rel).then((res) => { setResults(res.data); setLoading(false); })
    );
  }

  const count = results?.data?.length ?? 0;
  const total = results?.total ?? 0;

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container">
          <header className="ui-header">
            <div>
              <p className="ui-eyebrow">Browse</p>
              <h1 className="ui-title">Find a provider</h1>
              <p className="ui-sub">
                {loading && 'Searching…'}
                {!loading && results && total > 0 &&
                  `${total} provider${total === 1 ? '' : 's'} match your filters.`}
                {!loading && results && total === 0 &&
                  'No providers match those filters yet.'}
                {!loading && !results &&
                  'Filter by category, location, and budget.'}
              </p>
            </div>
          </header>

          <section className="search__filters">
            <div className="search__filter search__filter--wide">
              <label className="search__label" htmlFor="f-keyword">Search</label>
              <input id="f-keyword" className="search__input" name="keyword"
                placeholder="Decorator, photographer, MC…"
                value={filters.keyword} onChange={updateFilter} />
            </div>

            <div className="search__filter">
              <label className="search__label" htmlFor="f-cat">Category</label>
              <select id="f-cat" className="search__input" name="category_id"
                value={filters.category_id} onChange={updateFilter}>
                <option value="">All</option>
                {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>

            <div className="search__filter">
              <label className="search__label" htmlFor="f-loc">Location</label>
              <input id="f-loc" className="search__input" name="location"
                placeholder="Kampala"
                value={filters.location} onChange={updateFilter} />
            </div>

            <div className="search__filter">
              <label className="search__label" htmlFor="f-min">Budget (UGX)</label>
              <div className="search__price">
                <input id="f-min" className="search__input" name="price_min" type="number"
                  placeholder="Min" value={filters.price_min} onChange={updateFilter} />
                <span className="search__price-sep">–</span>
                <input className="search__input" name="price_max" type="number"
                  placeholder="Max" value={filters.price_max} onChange={updateFilter} />
              </div>
            </div>

            <div className="search__filter">
              <label className="search__label" htmlFor="f-sort">Sort</label>
              <select id="f-sort" className="search__input" name="sort"
                value={filters.sort} onChange={updateFilter}>
                <option value="rating">Highest rated</option>
                <option value="newest">Newest</option>
                <option value="price_low">Price: low to high</option>
                <option value="price_high">Price: high to low</option>
              </select>
            </div>
            <button
  type="button"
  className="search__submit"
  onClick={() => {
    const clean = Object.fromEntries(
      Object.entries(filters).filter(([, v]) => v !== '')
    );
    setLoading(true);
    searchProviders(clean).then(setResults).finally(() => setLoading(false));
  }}
>
  Search
</button>
          </section>

          {!loading && results?.data?.length === 0 && (
            <div className="ui-empty">
              <p className="ui-empty__title">No providers match yet.</p>
              <p className="ui-empty__text">Try widening your filters or clearing the search.</p>
              <button className="ui-btn ui-btn--ghost" onClick={() => setFilters(initialFilters)}>
                Clear filters
              </button>
            </div>
          )}

          {count > 0 && (
            <div className="search__grid">
              {results.data.map((p) => (
              <Link key={p.id} to={`/providers/${p.id}`} className="provider-card">
  <div className="provider-card__media">
    {p.profile_photo_path ? (
      <img src={`${STORAGE_BASE}/storage/${p.profile_photo_path}`} alt={p.business_name} />
    ) : (
      <span className="provider-card__placeholder">{p.business_name?.[0] ?? '?'}</span>
    )}

    {/* Heart Button (Using SVG for custom colors) */}
   <button
  type="button"
  className="favorite-btn"
  onClick={(e) => toggleFavorite(e, p.id)}
>
  <svg 
    viewBox="0 0 24 24" 
    width="28" 
    height="28" 
    fill={isFavorited(p.id) ? '#e11d48' : 'none'} 
    stroke={isFavorited(p.id) ? '#e11d48' : '#ffffff'} 
    strokeWidth="2.5" 
    strokeLinecap="round" 
    strokeLinejoin="round"
    style={{ transition: 'all 0.2s ease' }}
  >
    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
  </svg>
</button>

    {/* Verified Badge */}
    {p.verification_status === 'verified' && (
      <span className="verified-badge">✓ Verified</span>
    )}
  </div>
  
  <div className="provider-card__body">
    <h3 className="provider-card__name">{p.business_name}</h3>
    <p className="provider-card__meta">
      {p.category?.name ?? 'Provider'} · {p.location ?? 'Location not set'}
    </p>
    <div className="provider-card__footer">
                      {p.rating_avg ? (
                        <span className="provider-card__rating">
                          ★ {Number(p.rating_avg).toFixed(1)}
                          <span className="provider-card__reviews"> ({p.rating_count})</span>
                        </span>
                      ) : (
                        <span className="provider-card__rating provider-card__rating--empty">New</span>
                      )}
                      {(p.price_range_min || p.price_range_max) && (
                        <span className="provider-card__price">
                          UGX {p.price_range_min ?? '—'}–{p.price_range_max ?? '—'}
                        </span>
                      )}
                    </div>
  </div>
</Link>
              ))}
            </div>
          )}

          {results && results.last_page > 1 && (
            <nav className="search__pagination">
              <button className="ui-btn ui-btn--ghost" disabled={!results.prev_page_url}
                onClick={() => goToPage(results.prev_page_url)}>← Previous</button>
              <span className="search__pagination-info">
                Page {results.current_page} of {results.last_page}
              </span>
              <button className="ui-btn ui-btn--ghost" disabled={!results.next_page_url}
                onClick={() => goToPage(results.next_page_url)}>Next →</button>
            </nav>
          )}
        </div>
      </main>
    </>
  );
}


 