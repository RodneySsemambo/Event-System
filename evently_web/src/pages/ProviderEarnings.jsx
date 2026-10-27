import { useEffect, useState } from 'react';
import Navbar from '../components/Navbar';
import { fetchEarnings, requestWithdrawal } from '../api/withdrawals';
import './ProviderEarnings.css';
import api from '../api/axios';

export default function ProviderEarnings() {
  const [data, setData] = useState(null);
  const [phone, setPhone] = useState('');
  const [amount, setAmount] = useState('');
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [marzpayDown, setMarzpayDown] = useState(false);

 function load() {
  setError(null);
  fetchEarnings()
    .then(setData)
    .catch((err) => setError(err.response?.data?.message || 'Could not load your earnings.'));

  // Check MarzPay health on every load
  api.get('/api/system/marzpay-status')
    .then((res) => setMarzpayDown(res.data.status === 'down'))
    .catch(() => {});
}

  useEffect(load, []);

  async function handleWithdraw(e) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    setSuccess(null);

    try {
      const w = await requestWithdrawal(Number(amount), phone);
      setSuccess(`Withdrawal of UGX ${Number(w.amount).toLocaleString()} submitted.`);
      setAmount('');
      load();
    } catch (err) {
      setError(
        err.response?.data?.errors?.amount?.[0] ||
        err.response?.data?.errors?.phone_number?.[0] ||
        err.response?.data?.message ||
        'Could not submit withdrawal.'
      );
    } finally {
      setSubmitting(false);
    }
  }

  if (!data) {
  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--narrow">
          {error ? (
            <div className="ui-alert ui-alert--error">
              {error}
              <button
                className="ui-btn ui-btn--ghost ui-btn--sm"
                style={{ marginLeft: 12 }}
                onClick={load}
              >
                Retry
              </button>
            </div>
          ) : (
            <p className="ui-sub">Loading…</p>
          )}
        </div>
      </main>
    </>
  );
}

  return (
    <>
      <Navbar forceSolid />
      <main className="ui-page">
        <div className="ui-container--narrow">
          <header className="ui-header">
            <div>
              <p className="ui-eyebrow">Earnings</p>
              <h1 className="ui-title">Your balance</h1>
              <p className="ui-sub">
                Money earned from completed bookings, minus Evently's 10% commission.
              </p>
            </div>
          </header>

          <section className="earnings__stats">
            <div className="earnings__stat">
              <span className="earnings__label">Available to withdraw</span>
              <span className="earnings__value">
                UGX {data.balance.available.toLocaleString()}
              </span>
            </div>
            <div className="earnings__stat earnings__stat--muted">
              <span className="earnings__label">Total earned</span>
              <span className="earnings__value">
                UGX {data.balance.earned.toLocaleString()}
              </span>
            </div>
            <div className="earnings__stat earnings__stat--muted">
              <span className="earnings__label">Total withdrawn</span>
              <span className="earnings__value">
                UGX {data.balance.withdrawn.toLocaleString()}
              </span>
            </div>
          </section>

          {marzpayDown && (
  <div className="ui-alert ui-alert--error" style={{ marginBottom: 20 }}>
    Our payment provider is currently unreachable. Withdrawals may not work
    right now — try again in a few minutes.
  </div>
)}
          {data.balance.available >= data.minimum && (
            <form className="earnings__form" onSubmit={handleWithdraw}>
              <h2 className="earnings__form-title">Withdraw to mobile money</h2>
              <p className="earnings__form-sub">
                Minimum withdrawal: UGX {data.minimum.toLocaleString()}. Funds
                arrive within a few minutes.
              </p>

              {error && <div className="ui-alert ui-alert--error">{error}</div>}
              {success && <div className="ui-alert ui-alert--success">{success}</div>}

              <label className="ui-field">
                <span className="ui-field__label">Amount (UGX)</span>
                <input
                  type="number"
                  className="ui-field__input"
                  value={amount}
                  onChange={(e) => setAmount(e.target.value)}
                  min={data.minimum}
                  max={data.balance.available}
                  placeholder={data.balance.available.toString()}
                />
              </label>

              <label className="ui-field">
                <span className="ui-field__label">Mobile money number</span>
                <input
                  type="tel"
                  className="ui-field__input"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value.trim())}
                  placeholder="+256762379935"
                  maxLength={13}
                />
                <p className="ui-field__hint">
                  MTN or Airtel. Format: +256XXXXXXXXX.
                </p>
              </label>

              <button
                type="submit"
                className="ui-btn ui-btn--primary"
                disabled={submitting || !amount || !/^\+256[0-9]{9}$/.test(phone)}
              >
                {submitting ? 'Submitting…' : 'Withdraw'}
              </button>
            </form>
          )}

          {data.balance.available < data.minimum && (
            <div className="ui-alert ui-alert--info">
              You need at least UGX {data.minimum.toLocaleString()} to withdraw. Complete more bookings to build up your balance.
            </div>
          )}

          <section className="earnings__history">
            <h2 className="earnings__form-title">Withdrawal history</h2>
            {data.history.length === 0 ? (
              <p className="ui-sub">No withdrawals yet.</p>
            ) : (
              <ul className="earnings__list">
                {data.history.map((w) => (
                  <li key={w.id} className="earnings__list-item">
                    <div>
                      <p className="earnings__list-amount">
                        UGX {Number(w.amount).toLocaleString()}
                      </p>
                      <p className="earnings__list-meta">
                        {w.phone_number} · {new Date(w.created_at).toLocaleString()}
                      </p>
                    </div>
                    <span className={`ui-badge ui-badge--${w.status}`}>
                      {w.status}
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </section>
        </div>
      </main>
    </>
  );
}