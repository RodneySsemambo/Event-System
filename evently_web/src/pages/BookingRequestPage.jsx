import { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { fetchProvider } from '../api/providers';
import BookingRequestForm from '../components/BookingRequestForm';
import './BookingRequestPage.css';

const STORAGE_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export default function BookingRequestPage() {
  const { providerId } = useParams();
  const navigate = useNavigate();
  const [provider, setProvider] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchProvider(providerId)
      .then(setProvider)
      .finally(() => setLoading(false));
  }, [providerId]);

  if (loading) {
    return (
      <>
        <Navbar forceSolid />
        <main className="ui-page">
          <div className="ui-container--form">
            <p className="ui-sub">Loading…</p>
          </div>
        </main>
      </>
    );
  }

  if (!provider) {
    return (
      <>
        <Navbar forceSolid />
        <main className="ui-page">
          <div className="ui-container--form">
            <div className="ui-empty">
              <p className="ui-empty__title">Provider not found.</p>
              <Link to="/search" className="ui-btn ui-btn--ghost">Back to search</Link>
            </div>
          </div>
        </main>
      </>
    );
  }

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--form">
          <Link to={`/providers/${provider.id}`} className="booking__back">
            ← Back to profile
          </Link>

          <header className="booking__head">
            <p className="ui-eyebrow">Request a booking</p>
            <h1 className="ui-title">{provider.business_name}</h1>
            <p className="ui-sub">
              Send your event details. The provider confirms, then you pay.
            </p>
          </header>

          <aside className="booking__summary">
            <div className="booking__summary-media">
              {provider.profile_photo_path ? (
                <img
                  src={`${STORAGE_BASE}/storage/${provider.profile_photo_path}`}
                  alt={provider.business_name}
                />
              ) : (
                <span className="booking__summary-placeholder">
                  {provider.business_name?.[0] ?? '?'}
                </span>
              )}
            </div>
            <div className="booking__summary-info">
              <p className="booking__summary-category">
                {provider.category?.name ?? 'Provider'} · {provider.location ?? 'Location not set'}
              </p>
              {provider.reviews_avg_rating && (
                <p className="booking__summary-rating">
                  ★ {Number(provider.reviews_avg_rating).toFixed(1)}
                  <span className="booking__summary-count">
                    {' '}· {provider.reviews_count ?? 0} review{(provider.reviews_count ?? 0) === 1 ? '' : 's'}
                  </span>
                </p>
              )}
              {(provider.price_range_min || provider.price_range_max) && (
                <p className="booking__summary-price">
                  UGX {provider.price_range_min ?? '—'} – {provider.price_range_max ?? '—'}
                </p>
              )}
            </div>
          </aside>

          <BookingRequestForm
            providerId={provider.id}
            onSuccess={() => navigate('/bookings', { replace: true })}
          />
        </div>
      </main>
    </>
  );
}