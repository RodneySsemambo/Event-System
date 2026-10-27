import { motion, AnimatePresence } from 'framer-motion';
import './BookingModal.css';

export default function BookingModal({ booking, onClose, isProvider }) {
  if (!booking) return null;

  const otherParty = isProvider
    ? booking.client_profile?.user?.name
    : booking.provider_profile?.business_name;

  return (
    <AnimatePresence>
      <div className="modal-backdrop" onClick={onClose}>
        <motion.div 
          className="modal-content"
          onClick={(e) => e.stopPropagation()} // prevent close when clicking inside
          initial={{ opacity: 0, y: 20, scale: 0.95 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: 20, scale: 0.95 }}
        >
          <header className="modal-header">
            <div>
              <p className="ui-eyebrow">{booking.status}</p>
              <h2 className="modal-title">{booking.event_type}</h2>
            </div>
            <button className="modal-close" onClick={onClose}>×</button>
          </header>

          <div className="modal-body">
            <div className="modal-grid">
              <div>
                <span className="modal-label">Date</span>
                <p>{new Date(booking.event_date).toLocaleDateString('en-UG', { dateStyle: 'long' })}</p>
              </div>
              <div>
                <span className="modal-label">Location</span>
                <p>{booking.location}</p>
              </div>
              <div>
                <span className="modal-label">Guests</span>
                <p>{booking.guest_count}</p>
              </div>
              <div>
                <span className="modal-label">{isProvider ? 'Client' : 'Provider'}</span>
                <p>{otherParty}</p>
              </div>
              {booking.quoted_amount && (
                <div>
                  <span className="modal-label">Total Amount</span>
                  <p>UGX {Number(booking.quoted_amount).toLocaleString()}</p>
                </div>
              )}
              <div>
                <span className="modal-label">Requested On</span>
                <p>{new Date(booking.created_at).toLocaleDateString()}</p>
              </div>
            </div>

            {booking.notes && (
              <div className="modal-notes">
                <span className="modal-label">Notes</span>
                <p>{booking.notes}</p>
              </div>
            )}
          </div>
        </motion.div>
      </div>
    </AnimatePresence>
  );
}