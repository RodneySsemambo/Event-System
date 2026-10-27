import { useEffect, useRef, useState } from 'react';
import { payBooking, fetchPaymentStatus } from '../api/bookings';
import './PaymentForm.css';

const POLL_INTERVAL = 4000;
const POLL_TIMEOUT = 120000;

export default function PaymentForm({ booking, onSuccess, onCancel }) {
  const [phone, setPhone] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [stage, setStage] = useState('form'); 
  const [payment, setPayment] = useState(null);
  const [error, setError] = useState(null);

  const pollRef = useRef(null);
  const timeoutRef = useRef(null);

  useEffect(() => {
    return () => {
      clearInterval(pollRef.current);
      clearTimeout(timeoutRef.current);
    };
  }, []);

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      const p = await payBooking(booking.id, phone);
      setPayment(p);
      setStage('waiting');
      startPolling();
    } catch (err) {
      const msg =
        err.response?.data?.errors?.phone_number?.[0] ||
        err.response?.data?.errors?.payment?.[0] ||
        err.response?.data?.errors?.booking?.[0] ||
        err.response?.data?.message ||
        'Could not start payment. Please try again.';
      setError(msg);
    } finally {
      setSubmitting(false);
    }
  }

  function startPolling() {
    pollRef.current = setInterval(async () => {
      try {
        const current = await fetchPaymentStatus(booking.id);
        setPayment(current);

        if (current.status === 'successful') {
          stopPolling();
          onSuccess?.(current);
        } else if (current.status === 'failed') {
          stopPolling();
          setStage('failed');
          setError(current.failure_reason || 'The payment was not completed.');
        }
      } catch {
        // ignore network hiccups
      }
    }, POLL_INTERVAL);

    timeoutRef.current = setTimeout(() => {
      stopPolling();
      setStage('failed');
      setError('We did not receive a response from your mobile money provider. Check your phone and try again.');
    }, POLL_TIMEOUT);
  }

  function stopPolling() {
    clearInterval(pollRef.current);
    clearTimeout(timeoutRef.current);
  }

  function retry() {
    setStage('form');
    setError(null);
    setPayment(null);
    setPhone('');
  }

  if (stage === 'waiting') {
    return (
      <div className="payment-form">
        <h3 className="payment-form__title">Waiting for your approval</h3>
        <p className="payment-form__sub">
          We sent a prompt to <strong>{phone}</strong>. Open your phone and approve
          the payment of <strong>UGX {Number(booking.quoted_amount ?? booking.provider_profile?.price_range_min ?? 0).toLocaleString()}</strong>.
        </p>
        <div className="payment-form__status">
          <span className="payment-form__spinner" />
          <span>Waiting for confirmation…</span>
        </div>
        <p className="payment-form__hint">
          This usually takes a few seconds. If you don't see the prompt, check your mobile money app.
        </p>
        <div className="payment-form__actions">
          <button type="button" className="ui-btn ui-btn--ghost" onClick={() => { stopPolling(); setStage('failed'); setError('You closed the prompt. Click "Try again" to send another request.'); }}>
            Cancel
          </button>
        </div>
      </div>
    );
  }

  if (stage === 'failed') {
    const reason = (error || '').toLowerCase();
    const looksLikeNetworkOrTimeout = reason.includes('did not receive');
    const looksLikeBalanceIssue = reason.includes('balance') || reason.includes('insufficient') || reason.includes('funds');

    let title = 'Payment not completed';
    let body = error;

    if (looksLikeNetworkOrTimeout) {
      title = 'No response from the provider';
      body = 'The prompt may not have reached your phone. Check your mobile money app and try again.';
    } else if (looksLikeBalanceIssue) {
      title = 'Insufficient funds';
      body = 'Your mobile money wallet does not have enough balance for this payment. Top up and try again.';
    }

    return (
      <div className="payment-form">
        <h3 className="payment-form__title">{title}</h3>
        <div className="ui-alert ui-alert--error">{body}</div>
        <div className="payment-form__actions">
          <button type="button" className="ui-btn ui-btn--ghost" onClick={onCancel}>
            Cancel
          </button>
          <button type="button" className="ui-btn ui-btn--primary" onClick={retry}>
            Try again
          </button>
        </div>
      </div>
    );
  }

  return (
    <form className="payment-form" onSubmit={handleSubmit}>
      <h3 className="payment-form__title">Pay for this booking</h3>
      <p className="payment-form__sub">
        Enter the mobile money number you want to pay from. Amount:{' '}
        <strong>UGX {Number(booking.quoted_amount ?? booking.provider_profile?.price_range_min ?? 0).toLocaleString()}</strong>
      </p>

      {error && <div className="ui-alert ui-alert--error">{error}</div>}

      <label className="ui-field">
        <span className="ui-field__label">Mobile money number</span>
        <input
          type="tel"
          className="ui-field__input"
          placeholder="+256700000000"
          value={phone}
          onChange={(e) => setPhone(e.target.value.trim())}
          autoComplete="tel"
          inputMode="tel"
          maxLength={13}
        />
        <p className="ui-field__hint">
          MTN or Airtel. Format: +256XXXXXXXXX.
        </p>
      </label>

      <div className="payment-form__actions">
        <button type="button" className="ui-btn ui-btn--ghost" onClick={onCancel} disabled={submitting}>
          Cancel
        </button>
        <button type="submit" className="ui-btn ui-btn--primary" disabled={submitting || !/^\+256[0-9]{9}$/.test(phone)}>
          {submitting ? 'Sending prompt…' : 'Pay now'}
        </button>
      </div>
    </form>
  );
}