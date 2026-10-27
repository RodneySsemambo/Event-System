import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { fetchProvider } from '../api/providers';
import { useAuth } from '../context/AuthContext';
import './ProviderPublicProfile.css';


const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export default function ProviderPublicProfile() {
  const { id } = useParams();
  const { user } = useAuth();
  const [provider, setProvider] = useState(null);
  const [activeMedia, setActiveMedia] = useState(null);

  useEffect(() => { fetchProvider(id).then(setProvider); }, [id]);
  useEffect(() => {
  if (provider?.portfolio_items?.length > 0) {
    setActiveMedia(provider.portfolio_items[0]);
  }
}, [provider]);

  if (!provider) {
    return (
      <>
        <Navbar forceSolid />
        <main className="ui-page">
          <div className="ui-container--narrow">
            <p className="profile__loading">Loading…</p>
          </div>
        </main>
      </>
    );
  }

  const canBook = user?.role === 'client';
  const rating = provider.reviews_avg_rating
    ? Number(provider.reviews_avg_rating).toFixed(1)
    : null;
  const reviewCount = provider.reviews_count ?? 0;

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--narrow">
          <Link to="/search" className="profile__back">← Back to search</Link>

          {provider.banner_path && (
            <div className="profile__banner">
              <img src={`${STORAGE_BASE}/storage/${provider.banner_path}`} alt="" />
            </div>
          )}

          <header className="profile__header">
            <div className="profile__photo">
              {provider.profile_photo_path ? (
                <img src={`${STORAGE_BASE}/storage/${provider.profile_photo_path}`} alt={provider.business_name} />
              ) : (
                <span className="profile__photo-placeholder">{provider.business_name?.[0] ?? '?'}</span>
              )}
            </div>
            <div className="profile__head-info">
              <p className="profile__category">
                {provider.category?.name ?? 'Provider'} · {provider.location ?? 'Location not set'}
              </p>
              <h1 className="profile__name">{provider.business_name}</h1>
              <div className="profile__meta">
                {rating ? (
                  <span className="profile__rating">
                    ★ {rating}
                    <span className="profile__rating-count">
                      {' '}· {reviewCount} review{reviewCount === 1 ? '' : 's'}
                    </span>
                  </span>
                ) : (
                  <span className="profile__rating profile__rating--empty">New to Evently</span>
                )}
                {provider.availability_status && (
                  <span className={`profile__availability profile__availability--${provider.availability_status}`}>
                    {provider.availability_status}
                  </span>
                )}
              </div>
            </div>
          </header>

          {(provider.price_range_min || provider.price_range_max) && (
            <div className="profile__price-strip">
              <span className="profile__price-label">Typical Booking price range</span>
              <span className="profile__price-value">
                UGX {provider.price_range_min ?? '—'} – {provider.price_range_max ?? '—'}
              </span>
            </div>
          )}

          {provider.bio && (
            <section className="profile__section">
              <h2 className="profile__section-title">About</h2>
              <p className="profile__bio">{provider.bio}</p>
            </section>
          )}

          <section className="profile__actions">
  {user?.role === 'client' && (
    <Link to={`/book/${provider.id}`} className="ui-btn ui-btn--primary">
      Book appointment
    </Link>
  )}
  {!user && (
    <Link to="/login" className="ui-btn ui-btn--primary">
      Log in to book
    </Link>
  )}
  {user?.role === 'provider' && (
    <span className="profile__notice">
      Only clients can book providers.
    </span>
  )}
</section>

        
          {provider.portfolio_items?.length > 0 && activeMedia && (
  <section className="profile__section">
    <h2 className="profile__section-title">Recent Work</h2>
    
    <div className="portfolio-gallery">
      {/* Main Viewer */}
      <div className="portfolio-gallery__main">
        {activeMedia.media_type === 'video' ? (
          <video 
            src={`${STORAGE_BASE}/storage/${activeMedia.image_path}`} 
            controls 
            autoPlay 
            muted 
            playsInline 
          />
        ) : (
          <img 
            src={`${STORAGE_BASE}/storage/${activeMedia.image_path}`} 
            alt={activeMedia.caption || 'Portfolio image'} 
          />
        )}
        {activeMedia.caption && (
          <div className="portfolio-gallery__caption">
            {activeMedia.caption}
          </div>
        )}
      </div>

      {/* Thumbnails */}
      <div className="portfolio-gallery__thumbs">
        {provider.portfolio_items.map((item) => (
          <button
            key={item.id}
            className={`portfolio-thumb ${activeMedia.id === item.id ? 'portfolio-thumb--active' : ''}`}
            onClick={() => setActiveMedia(item)}
          >
            {item.media_type === 'video' ? (
              <div className="portfolio-thumb__video">
                <video src={`${STORAGE_BASE}/storage/${item.image_path}`} />
                <span className="portfolio-thumb__play">▶</span>
              </div>
            ) : (
              <img src={`${STORAGE_BASE}/storage/${item.image_path}`} alt="Thumbnail" />
            )}
          </button>
        ))}
                </div>
                <h2 className="profile__section-title">Reviews</h2>
            {(!provider.reviews || provider.reviews.length === 0) ? (
              <p className="profile__empty">No reviews yet — be the first to book.</p>
            ) : (
              <ul className="profile__reviews">
                {provider.reviews.map((r) => (
                  <li key={r.id} className="review">
                    <div className="review__head">
                      <span className="review__stars">
                        {'★'.repeat(r.rating)}
                        <span className="review__stars-dim">{'★'.repeat(5 - r.rating)}</span>
                      </span>
                      <span className="review__author">
                        {r.client_profile?.user?.name || 'Anonymous'}
                      </span>
                    </div>
                    {r.comment && <p className="review__body">{r.comment}</p>}
                  </li>
                ))}
              </ul>
            )}
    </div>
  </section>
)}
        </div>
      </main>
    </>
  );
}
 