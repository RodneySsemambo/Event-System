import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { fetchNotifications, markNotificationRead, markAllRead } from '../api/notifications';
import './NotificationBell.css';

export default function NotificationBell() {
  const [open, setOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [unread, setUnread] = useState(0);
  const ref = useRef(null);
  const navigate = useNavigate();

  function load() {
    fetchNotifications().then((res) => {
      setNotifications(res.data);
      setUnread(res.unread_count);
    });
  }

  useEffect(() => {
    load();
    const interval = setInterval(load, 30000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    function onClickOutside(e) {
      if (ref.current && !ref.current.contains(e.target)) setOpen(false);
    }
    document.addEventListener('mousedown', onClickOutside);
    return () => document.removeEventListener('mousedown', onClickOutside);
  }, []);

  async function handleRead(id) {
    await markNotificationRead(id);
    setNotifications((prev) =>
      prev.map((n) => (n.id === id ? { ...n, read_at: new Date().toISOString() } : n))
    );
    setUnread((c) => Math.max(0, c - 1));
  }

  async function handleReadAll() {
    await markAllRead();
    setNotifications((prev) => prev.map((n) => ({ ...n, read_at: new Date().toISOString() })));
    setUnread(0);
  }

  async function handleItemClick(n) {
    if (!n.read_at) {
      await handleRead(n.id);
    }

  const bookingId = n.data?.booking_id;

    setOpen(false);

    if (bookingId) {
      navigate(`/bookings?highlight=${bookingId}`);
    } else {
      navigate('/notifications');
    }
  }

 function describe(n) {
  const d = n.data || {};
  switch (d.event) {
    case 'booking_created':     return `New booking request: ${d.event_type}`;
    case 'booking_accepted':    return `Booking accepted: ${d.event_type}`;
    case 'booking_declined':    return `Booking declined: ${d.event_type}`;
    case 'booking_completed':   return `Booking completed: ${d.event_type}`;
    case 'payment_successful':  return `Payment received for booking #${d.booking_id}`;
    case 'payment_failed':      return `Payment failed for booking #${d.booking_id}`;
    default:                    return 'New notification';
  }
}

  return (
    <div className="notif" ref={ref}>
      <button
        type="button"
        className="notif__btn"
        onClick={() => setOpen((o) => !o)}
        aria-label="Notifications"
      >
        🔔
        {unread > 0 && <span className="notif__badge">{unread}</span>}
      </button>

      {open && (
        <div className="notif__panel">
          <div className="notif__panel-head">
            <span>Notifications</span>
            {unread > 0 && (
              <button className="notif__mark-all" onClick={handleReadAll}>
                Mark all read
              </button>
            )}
          </div>

          {notifications.length === 0 && (
            <p className="notif__empty">No notifications yet.</p>
          )}

          <ul className="notif__list">
            {notifications.map((n) => (
              <li
                key={n.id}
                className={`notif__item ${n.read_at ? '' : 'notif__item--unread'}`}
                onClick={() => handleItemClick(n)}
                role="button"
                tabIndex={0}
              >
                <p className="notif__item-title">{describe(n)}</p>
                <span className="notif__item-time">
                  {new Date(n.created_at).toLocaleString()}
                </span>
              </li>
            ))}
          </ul>

          <div className="notif__panel-foot">
            <Link to="/notifications" onClick={() => setOpen(false)}>
              View all notifications
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}