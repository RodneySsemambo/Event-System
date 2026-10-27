import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { useAuth } from '../context/AuthContext';
import { fetchMyBookings } from '../api/bookings';
import './Dashboard.css';

export default function Dashboard() {
  const { user } = useAuth();
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchMyBookings()
      .then((res) => {
        const items = Array.isArray(res) ? res : res.data || [];
        setBookings(items);
      })
      .catch(() => setBookings([]))
      .finally(() => setLoading(false));
  }, []);

  const firstName = user.name.split(' ')[0];
  const isProvider = user.role === 'provider';

  const pending = bookings.filter((b) => b.status === 'pending').length;
  const accepted = bookings.filter((b) => b.status === 'accepted').length;
  const completed = bookings.filter((b) => b.status === 'completed').length;

  const recent = bookings.slice(0, 3);
console.log('DASHBOARD ROLE:', user?.role);
  return (
    <div className="dash">
      <Navbar forceSolid />

      <main className="dash__main">
        <header className="dash__header">
          <div>
            <p className="dash__eyebrow">
              {isProvider ? 'Provider dashboard' : 'Client dashboard'}
            </p>
            <h1 className="dash__heading">Welcome back, {firstName}.</h1>
            <p className="dash__sub">
              {isProvider
                ? 'Here is what is happening with your bookings.'
                : 'Find providers, manage bookings, and plan your event.'}
            </p>
          </div>
          <Link to={isProvider ? '/profile/edit' : '/search'} className="dash__cta">
            {isProvider ? 'Edit profile' : 'Find a provider'}
          </Link>
        </header>

        <section className="dash__stats">
          <Stat label="Pending" value={pending} loading={loading} />
          <Stat label="Accepted" value={accepted} loading={loading} />
          <Stat label="Completed" value={completed} loading={loading} />
          <Stat label="Total" value={bookings.length} loading={loading} />
        </section>

        <div className="dash__grid">
          <section className="dash__panel">
            <div className="dash__panel-head">
              <h2 className="dash__panel-title">Recent bookings</h2>
              <Link to="/bookings" className="dash__panel-link">View all</Link>
            </div>

            {recent.length === 0 ? (
              <div className="dash__empty">
                <p>No bookings yet.</p>
                {!isProvider && (
                  <Link to="/search" className="dash__empty-link">
                    Find your first provider
                  </Link>
                )}
              </div>
            ) : (
              <ul className="dash__list">
                {recent.map((b) => (
                  <li key={b.id} className="dash__list-item">
                    <div className="dash__list-info">
                      <p className="dash__list-title">
                        {b.provider?.business_name || b.provider?.user?.name || 'Booking'}
                      </p>
                      <p className="dash__list-meta">
                        {b.event_date
                          ? new Date(b.event_date).toLocaleDateString()
                          : 'Date not set'}
                      </p>
                    </div>
                    <span className={`dash__badge dash__badge--${b.status}`}>
                      {b.status}
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </section>

          <section className="dash__panel">
            <div className="dash__panel-head">
              <h2 className="dash__panel-title">Quick actions</h2>
            </div>

            <div className="dash__actions">
              {isProvider ? (
                <>
                  <Link to="/profile/edit" className="dash__action">
                    <span className="dash__action-title">Edit your profile</span>
                    <span className="dash__action-hint">Portfolio, pricing, availability</span>
                  </Link>
                  <Link to="/bookings" className="dash__action">
                    <span className="dash__action-title">Review booking requests</span>
                    <span className="dash__action-hint">Accept, reject, or message clients</span>
                  </Link>
                </>
              ) : (
                <>
                  <Link to="/search" className="dash__action">
                    <span className="dash__action-title">Search providers</span>
                    <span className="dash__action-hint">Filter by category and location</span>
                  </Link>
                  <Link to="/bookings" className="dash__action">
                    <span className="dash__action-title">My bookings</span>
                    <span className="dash__action-hint">Track requests and past events</span>
                  </Link>
                </>
              )}
            </div>
          </section>
        </div>
      </main>
    </div>
  );
}

function Stat({ label, value, loading }) {
  return (
    <div className="dash__stat">
      <span className="dash__stat-label">{label}</span>
      <span className="dash__stat-value">{loading ? '—' : value}</span>
    </div>
  );
}