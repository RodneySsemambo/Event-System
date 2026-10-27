import { useState } from 'react';
import { updateBookingStatus } from '../api/bookings';
import './AcceptForm.css';

export default function AcceptForm({ booking, onAccepted, onCancel }) {
  const [amount, setAmount] = useState(booking.provider_profile?.price_range_min ?? '');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);

  async function handleAccept() {
    setSubmitting(true);
    setError(null);

    try {
      const updated = await updateBookingStatus(booking.id, 'accepted', Number(amount));
      onAccepted(updated);
    } catch (err) {
      setError(
        err.response?.data?.message ||
        err.response?.data?.errors?.quoted_amount?.[0] ||
        'Could not accept the booking.'
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="accept-form">
      <label className="ui-field">
        <span className="ui-field__label">Your price for this event (UGX)</span>
        <input
          type="number"
          className="ui-field__input"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          min="100"
          placeholder="e.g. 500000"
          autoFocus
        />
        <p className="ui-field__hint">
          This is what the client will pay. Set it clearly before accepting.
        </p>
      </label>

      {error && <div className="ui-alert ui-alert--error">{error}</div>}

      <div className="accept-form__actions">
        <button
          type="button"
          className="ui-btn ui-btn--ghost ui-btn--sm"
          onClick={onCancel}
          disabled={submitting}
        >
          Cancel
        </button>
        <button
          type="button"
          className="ui-btn ui-btn--primary ui-btn--sm"
          onClick={handleAccept}
          disabled={submitting || !amount || Number(amount) < 100}
        >
          {submitting ? 'Accepting…' : 'Accept at this price'}
        </button>
      </div>
    </div>
  );
}