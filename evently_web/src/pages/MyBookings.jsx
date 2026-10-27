import { useEffect, useState, useRef } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { useAuth } from '../context/AuthContext';
import {
  fetchMyBookings,
  updateBookingStatus,
  cancelBooking,
  completeBooking,
} from '../api/bookings';
import ReviewForm from '../components/ReviewForm';
import WhatsAppButton from '../components/WhatsAppButton';
import PaymentForm from '../components/PaymentForm';
import AcceptForm from '../components/AcceptForm';
import BookingModal from '../components/BookingModal';
import './MyBookings.css';

export default function MyBookings() {
  const { user } = useAuth();
  const [bookings, setBookings] = useState(null);
  const [reviewingId, setReviewingId] = useState(null);
  const [payingId, setPayingId] = useState(null);
  const [acceptingId, setAcceptingId] = useState(null);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [error, setError] = useState(null);

  const [searchParams] = useSearchParams();
  const highlightId = searchParams.get('highlight');
  const cardRefs = useRef({});

  function load() {
    fetchMyBookings()
      .then((res) => setBookings(res.data))
      .catch(() => setError('Could not load your bookings.'));
  }

  useEffect(load, []);

  useEffect(() => {
    if (!bookings || !highlightId) return;
    const el = cardRefs.current[highlightId];
    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.classList.add('booking-card--highlighted');

    const timer = setTimeout(() => {
      el.classList.remove('booking-card--highlighted');
    }, 2500);

    return () => clearTimeout(timer);
  }, [bookings, highlightId]);

  async function handleAction(action, booking, e) {
    if (e) e.stopPropagation();

    const actions = {
      decline: () => updateBookingStatus(booking.id, 'declined'),
      cancel: () => cancelBooking(booking.id),
      complete: () => completeBooking(booking.id),
    };

    try {
      const updated = await actions[action]();
      setBookings((prev) =>
        prev.map((b) => (b.id === updated.id ? { ...b, ...updated } : b))
      );
    } catch (err) {
      alert(
        err.response?.data?.errors?.payment?.[0] ||
        err.response?.data?.message ||
        'That action could not be completed.'
      );
    }
  }

  const isProvider = user?.role === 'provider';

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container">
          <header className="ui-header">
            <div>
              <p className="ui-eyebrow">{isProvider ? 'Incoming requests' : 'Your bookings'}</p>
              <h1 className="ui-title">Bookings</h1>
              <p className="ui-sub">
                {isProvider
                  ? 'Requests from clients. Accept to confirm the date.'
                  : 'Track your event requests and provider confirmations.'}
              </p>
            </div>
          </header>

          {error && !bookings && (
            <div className="ui-alert ui-alert--error">
              {error}
              <button
                className="ui-btn ui-btn--ghost ui-btn--sm"
                style={{ marginLeft: 12 }}
                onClick={() => { setError(null); setBookings(null); load(); }}
              >
                Retry
              </button>
            </div>
          )}

          {bookings === null && !error && (
            <p className="bookings__loading">Loading…</p>
          )}

          {bookings?.length === 0 && (
            <div className="ui-empty">
              <p className="ui-empty__title">No bookings yet.</p>
              <p className="ui-empty__text">
                {isProvider
                  ? 'When clients request your services, they will show up here.'
                  : 'Browse providers and send your first request.'}
              </p>
              {!isProvider && (
                <Link to="/search" className="ui-btn ui-btn--primary">Find a provider</Link>
              )}
            </div>
          )}

          {bookings?.length > 0 && (
            <div className="bookings__list">
              {bookings.map((booking) => {
                const otherParty = isProvider
                  ? booking.client_profile?.user?.name
                  : booking.provider_profile?.business_name;

                return (
                  <article 
                    key={booking.id} 
                    ref={(el) => (cardRefs.current[booking.id] = el)}
                    className="booking-card" 
                    onClick={() => setSelectedBooking(booking)}
                    style={{ cursor: 'pointer' }}
                  >
                    <header className="booking-card__head">
                      <div>
                        <h3 className="booking-card__title">{booking.event_type}</h3>
                        <p className="booking-card__meta">
                          {isProvider ? 'From' : 'Provider'}: {otherParty ?? '—'}
                        </p>
                      </div>
                      <span className={`ui-badge ui-badge--${booking.status}`}>
                        {booking.status}
                      </span>
                    </header>

                    <dl className="booking-card__details">
                      <div>
                        <dt>Date</dt>
                        <dd>
                          {booking.event_date
                            ? new Date(booking.event_date).toLocaleDateString('en-UG', {
                                day: 'numeric', month: 'short', year: 'numeric',
                              })
                            : 'Not set'}
                        </dd>
                      </div>
                      <div>
                        <dt>Location</dt>
                        <dd>{booking.location ?? 'Not set'}</dd>
                      </div>
                      <div>
                        <dt>Guests</dt>
                        <dd>{booking.guest_count ?? 'Not set'}</dd>
                      </div>
                    </dl>

                    {booking.notes && (
                      <p className="booking-card__notes">{booking.notes}</p>
                    )}

                    <div className="booking-card__actions" onClick={(e) => e.stopPropagation()}>
                      {isProvider && booking.status === 'pending' && (
                        <>
                          <button 
                            className="ui-btn ui-btn--primary ui-btn--sm" 
                            onClick={() => setAcceptingId(booking.id)}
                          >
                            Accept
                          </button>
                          <button 
                            className="ui-btn ui-btn--danger ui-btn--sm" 
                            onClick={(e) => handleAction('decline', booking, e)}
                          >
                            Decline
                          </button>
                        </>
                      )}

                      {isProvider && booking.status === 'accepted' && (
                        <button
                          className="ui-btn ui-btn--primary ui-btn--sm"
                          onClick={(e) => handleAction('complete', booking, e)}
                        >
                          Mark completed
                        </button>
                      )}

                      {!isProvider && booking.status === 'pending' && booking.payment?.status !== 'successful' && (
                        <>
                          <span className="booking-card__note">
                            Waiting for the provider to confirm. You'll be able to pay once accepted.
                          </span>
                          <button
                            className="ui-btn ui-btn--ghost ui-btn--sm"
                            onClick={(e) => handleAction('cancel', booking, e)}
                          >
                            Cancel request
                          </button>
                        </>
                      )}

                      {!isProvider && booking.status === 'accepted' && (
                        <>
                          {booking.payment?.status === 'successful' ? (
                            <>
                              <span className="booking-card__paid">Paid ✓</span>
                              {booking.provider_profile?.whatsapp_number && (
                                <WhatsAppButton
                                  whatsappNumber={booking.provider_profile.whatsapp_number}
                                  providerName={booking.provider_profile.business_name}
                                />
                              )}
                            </>
                          ) : booking.payment?.status === 'pending' ? (
                            <>
                              <span className="booking-card__pending">
                                Payment pending — approve the prompt on your phone
                              </span>
                              {booking.provider_profile?.whatsapp_number && (
                                <WhatsAppButton
                                  whatsappNumber={booking.provider_profile.whatsapp_number}
                                  providerName={booking.provider_profile.business_name}
                                />
                              )}
                            </>
                          ) : (
                            <>
                              <button
                                className="ui-btn ui-btn--primary ui-btn--sm"
                                onClick={() => setPayingId(booking.id)}
                              >
                                Pay now
                              </button>
                              <span className="booking-card__note">
                                Chat on WhatsApp unlocks once payment is confirmed.
                              </span>
                            </>
                          )}
                        </>
                      )}

                      {!isProvider && booking.status === 'completed' && (
                        <>
                          {booking.provider_profile?.whatsapp_number && (
                            <WhatsAppButton
                              whatsappNumber={booking.provider_profile.whatsapp_number}
                              providerName={booking.provider_profile.business_name}
                            />
                          )}
                          {!booking.review && reviewingId !== booking.id && (
                            <button
                              className="ui-btn ui-btn--primary ui-btn--sm"
                              onClick={() => setReviewingId(booking.id)}
                            >
                              Leave a review
                            </button>
                          )}
                          {booking.review && (
                            <span className="booking-card__reviewed">Reviewed ✓</span>
                          )}
                        </>
                      )}

                      {!isProvider && (
                        <Link
                          to={`/providers/${booking.provider_profile?.id}`}
                          className="ui-btn ui-btn--ghost ui-btn--sm"
                        >
                          View provider
                        </Link>
                      )}
                    </div>

                    {acceptingId === booking.id && (
                      <div className="booking-card__review-form" onClick={(e) => e.stopPropagation()}>
                        <AcceptForm
                          booking={booking}
                          onCancel={() => setAcceptingId(null)}
                          onAccepted={(updated) => {
                            setBookings((prev) =>
                              prev.map((b) => (b.id === updated.id ? { ...b, ...updated } : b))
                            );
                            setAcceptingId(null);
                          }}
                        />
                      </div>
                    )}

                    {reviewingId === booking.id && (
                      <div className="booking-card__review-form" onClick={(e) => e.stopPropagation()}>
                        <ReviewForm
                          bookingId={booking.id}
                          onSubmitted={(review) => {
                            setBookings((prev) =>
                              prev.map((b) => (b.id === booking.id ? { ...b, review } : b))
                            );
                            setReviewingId(null);
                          }}
                        />
                      </div>
                    )}

                    {payingId === booking.id && (
                      <div className="booking-card__review-form" onClick={(e) => e.stopPropagation()}>
                        <PaymentForm
                          booking={booking}
                          onCancel={() => setPayingId(null)}
                          onSuccess={(payment) => {
                            setBookings((prev) =>
                              prev.map((b) => (b.id === booking.id ? { ...b, payment } : b))
                            );
                            setPayingId(null);
                          }}
                        />
                      </div>
                    )}
                  </article>
                );
              })}
            </div>
          )}
        </div>
        
        <BookingModal 
          booking={selectedBooking} 
          onClose={() => setSelectedBooking(null)} 
          isProvider={isProvider} 
        />
      </main>
    </>
  );
}