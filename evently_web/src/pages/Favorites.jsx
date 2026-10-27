import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { fetchFavorites } from '../api/favorites';
import './Search.css'; // Reuse Search styles for the cards

const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export default function Favorites() {
  const [favorites, setFavorites] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchFavorites()
      .then((data) => setFavorites(data || []))
      .catch(() => setFavorites([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container">
          <header className="ui-header">
            <div>
              <p className="ui-eyebrow">Saved</p>
              <h1 className="ui-title">Your Favorites</h1>
              <p className="ui-sub">Providers you've saved for later.</p>
            </div>
          </header>

          {loading && <p className="bookings__loading">Loading…</p>}

          {!loading && favorites.length === 0 && (
            <div className="ui-empty">
              <p className="ui-empty__title">No favorites yet.</p>
              <p className="ui-empty__text">Tap the heart on any provider to save them here.</p>
              <Link to="/search" className="ui-btn ui-btn--primary">Find a provider</Link>
            </div>
          )}

          {!loading && favorites.length > 0 && (
            <div className="search__grid">
              {favorites.map((fav) => {
                const p = fav.provider_profile; // Based on the backend relation
                if (!p) return null;

                return (
                  <Link key={p.id} to={`/providers/${p.id}`} className="provider-card">
                    <div className="provider-card__media">
                      {p.profile_photo_path ? (
                        <img src={`${STORAGE_BASE}/storage/${p.profile_photo_path}`} alt={p.business_name} />
                      ) : (
                        <span className="provider-card__placeholder">{p.business_name?.[0] ?? '?'}</span>
                      )}
                      
                      {/* Full heart since it's in favorites */}
                      <span className="favorite-btn">❤️</span>

                      {p.verification_status === 'verified' && (
                        <span className="verified-badge">Verified</span>
                      )}
                    </div>
                    <div className="provider-card__body">
                      <h3 className="provider-card__name">{p.business_name}</h3>
                      <p className="provider-card__meta">
                        {p.category?.name ?? 'Provider'} · {p.location ?? 'Location not set'}
                      </p>
                    </div>
                  </Link>
                );
              })}
            </div>
          )}
        </div>
      </main>
    </>
  );
}