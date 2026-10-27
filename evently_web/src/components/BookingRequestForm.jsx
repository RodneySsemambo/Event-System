import { useState } from 'react';
import { createBooking } from '../api/bookings';
import './BookingRequestForm.css';

export default function BookingRequestForm({ providerId, onSuccess }) {
  const [form, setForm] = useState({
    event_type: '',
    event_date: '',
    location: '',
    guest_count: '',
    notes: '',
  });
  const [errors, setErrors] = useState({});
  const [submitting, setSubmitting] = useState(false);

  function updateField(e) {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});

    try {
      await createBooking({ provider_profile_id: providerId, ...form });
      onSuccess?.();
    } catch (err) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors || {});
      } else {
        setErrors({ general: [err.response?.data?.message || 'Something went wrong.'] });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="booking-form">
      {errors.general && (
        <div className="ui-alert ui-alert--error">{errors.general[0]}</div>
      )}

      <section className="booking-form__section">
        <h2 className="booking-form__section-title">Event details</h2>

        <label className="ui-field">
          <span className="ui-field__label">Event type</span>
          <input
            type="text"
            name="event_type"
            className="ui-field__input"
            placeholder="Wedding, corporate launch, birthday…"
            value={form.event_type}
            onChange={updateField}
          />
          {errors.event_type?.[0] && <p className="ui-field__error">{errors.event_type[0]}</p>}
        </label>

        <div className="booking-form__row">
          <label className="ui-field">
            <span className="ui-field__label">Event date</span>
            <input
              type="date"
              name="event_date"
              className="ui-field__input"
              value={form.event_date}
              onChange={updateField}
            />
            {errors.event_date?.[0] && <p className="ui-field__error">{errors.event_date[0]}</p>}
          </label>

          <label className="ui-field">
            <span className="ui-field__label">Guest count</span>
            <input
              type="number"
              name="guest_count"
              className="ui-field__input"
              min="1"
              placeholder="e.g. 150"
              value={form.guest_count}
              onChange={updateField}
            />
            {errors.guest_count?.[0] && <p className="ui-field__error">{errors.guest_count[0]}</p>}
          </label>
        </div>

        <label className="ui-field">
          <span className="ui-field__label">Location</span>
          <input
            type="text"
            name="location"
            className="ui-field__input"
            placeholder="Venue or area, e.g. Munyonyo"
            value={form.location}
            onChange={updateField}
          />
          {errors.location?.[0] && <p className="ui-field__error">{errors.location[0]}</p>}
        </label>

        <label className="ui-field">
          <span className="ui-field__label">Notes (optional)</span>
          <textarea
            name="notes"
            className="ui-field__textarea"
            rows={4}
            placeholder="Anything the provider should know — theme, setup time, special requests."
            value={form.notes}
            onChange={updateField}
          />
          {errors.notes?.[0] && <p className="ui-field__error">{errors.notes[0]}</p>}
        </label>
      </section>

      <div className="booking-form__actions">
        <button type="submit" className="ui-btn ui-btn--primary" disabled={submitting}>
          {submitting ? 'Sending…' : 'Send request'}
        </button>
      </div>

      <p className="booking-form__note">
        You won't be charged yet. The provider confirms availability, then you pay.
      </p>
    </form>
  );
}