import { useState } from 'react';
import { submitReview } from '../api/bookings';
import './ReviewForm.css';

export default function ReviewForm({ bookingId, onSubmitted }) {
  const [rating, setRating] = useState(5);
  const [hovered, setHovered] = useState(0);
  const [comment, setComment] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      const review = await submitReview(bookingId, rating, comment);
      onSubmitted(review);
    } catch (err) {
      setError(err.response?.data?.message || 'Could not submit review.');
    } finally {
      setSubmitting(false);
    }
  }

  const activeRating = hovered || rating;

  return (
    <form onSubmit={handleSubmit} className="review-form">
      <h3 className="review-form__title">Leave a review</h3>

      {error && <div className="ui-alert ui-alert--error">{error}</div>}

      <div className="review-form__stars">
        {[1, 2, 3, 4, 5].map((n) => (
          <button
            key={n}
            type="button"
            className={`review-form__star ${n <= activeRating ? 'review-form__star--active' : ''}`}
            onClick={() => setRating(n)}
            onMouseEnter={() => setHovered(n)}
            onMouseLeave={() => setHovered(0)}
            aria-label={`${n} star${n > 1 ? 's' : ''}`}
          >
            ★
          </button>
        ))}
        <span className="review-form__label">
          {activeRating} / 5
        </span>
      </div>

      <label className="ui-field">
        <span className="ui-field__label">Comment (optional)</span>
        <textarea
          className="ui-field__textarea"
          placeholder="How was your experience?"
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          rows={3}
          maxLength={1000}
        />
      </label>

      <div className="review-form__actions">
        <button type="submit" className="ui-btn ui-btn--primary" disabled={submitting}>
          {submitting ? 'Submitting…' : 'Submit review'}
        </button>
      </div>
    </form>
  );
}