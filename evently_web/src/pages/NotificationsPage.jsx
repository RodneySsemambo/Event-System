import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { fetchNotifications, markNotificationRead } from '../api/notifications';
import './NotificationsPage.css';

export default function NotificationsPage() {
  const navigate = useNavigate();
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchNotifications()
      .then((res) => setNotifications(res.data))
      .finally(() => setLoading(false));
  }, []);

  async function handleClick(n) {
    if (!n.read_at) {
      await markNotificationRead(n.id);
      setNotifications((prev) =>
        prev.map((x) => (x.id === n.id ? { ...x, read_at: new Date().toISOString() } : x))
      );
    }

    // Extract the booking id from whatever shape this notification uses.
    const bookingId = n.data?.booking_id ?? n.data?.booking;
    if (bookingId) {
      navigate(`/bookings?highlight=${bookingId}`);
    }
  }

  function describe(n) {
    const d = n.data || {};
    switch (d.event) {
      case 'booking_created':
        return `New booking request: ${d.event_type}`;
      case 'booking_accepted':
        return `Booking accepted: ${d.event_type}`;
      case 'booking_declined':
        return `Booking declined: ${d.event_type}`;
      case 'booking_completed':
        return `Booking completed: ${d.event_type}`;
      case 'Payment Confirmed':
        return `Payment received for booking #${d.booking}`;
      case 'payment_failed':
        return `Payment failed for booking #${d.booking}`;
      default:
        return 'New notification';
    }
  }

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--narrow">
          <header className="ui-header">
            <div>
              <p className="ui-eyebrow">Inbox</p>
              <h1 className="ui-title">Notifications</h1>
              <p className="ui-sub">Updates about your bookings and payments.</p>
            </div>
          </header>

          {loading && <p className="notif-page__loading">Loading…</p>}

          {!loading && notifications.length === 0 && (
            <div className="ui-empty">
              <p className="ui-empty__title">Nothing here yet.</p>
              <p className="ui-empty__text">
                You'll see booking and payment updates here.
              </p>
            </div>
          )}

          {!loading && notifications.length > 0 && (
            <ul className="notif-page__list">
              {notifications.map((n) => (
                <li
                  key={n.id}
                  className={`notif-page__item ${n.read_at ? '' : 'notif-page__item--unread'}`}
                  onClick={() => handleClick(n)}
                  role="button"
                  tabIndex={0}
                >
                  <div className="notif-page__dot" />
                  <div className="notif-page__body">
                    <p className="notif-page__title">{describe(n)}</p>
                    <p className="notif-page__time">
                      {new Date(n.created_at).toLocaleString()}
                    </p>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </div>
      </main>
    </>
  );
}