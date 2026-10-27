import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import './Auth.css';
import GoogleButton from '../components/GoogleButton';
import AuthDivider from '../components/AuthDivider';

export default function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  // Read the ?error= param from the Google callback
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('error') === 'google_failed') {
      setError('Google sign-in failed. Please try again or use email.');
    }
  }, []);

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      // Removed the turnstileToken argument
      await login(email, password);
      navigate('/dashboard');
    } catch (err) {
      setError(
        err.response?.status === 422
          ? err.response?.data?.errors?.email?.[0]
            || err.response?.data?.message
            || 'These credentials do not match our records.'
          : 'Something went wrong. Please try again.'
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="auth">
      <aside className="auth__brand">
        <Link to="/" className="auth__logo">Evently</Link>
        <div className="auth__brand-body">
          <Link to="/" className="auth__back" aria-label="Back to home">←</Link>
          <p className="auth__kicker">Welcome back</p>
          <h2 className="auth__headline">Pick up where you left off.</h2>
          <p className="auth__sub">
            Your bookings, providers, and messages — all in one place.
          </p>
        </div>
        <p className="auth__footer">Event services, found in minutes.</p>
      </aside>

      <main className="auth__main">
        <div className="auth__form-wrap">
          <h1 className="auth__title">Log in</h1>
          <p className="auth__subtitle">Enter your details to continue.</p>

          {error && <div className="auth__error">{error}</div>}

          <GoogleButton label="Continue with Google" />
          <AuthDivider />

          <form onSubmit={handleSubmit} className="auth__form">
            <label className="auth__field">
              <span className="auth__label">Email</span>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="auth__input"
                autoComplete="email"
                required
              />
            </label>

            <label className="auth__field">
              <span className="auth__label">Password</span>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="auth__input"
                autoComplete="current-password"
                required
              />
            </label>

            <button
              type="submit"
              className="auth__submit"
              disabled={submitting}
            >
              {submitting ? 'Logging in…' : 'Log in'}
            </button>
          </form>

          <p className="auth__switch">
            Don't have an account? <Link to="/register">Create one</Link>
          </p>
        </div>
      </main>
    </div>
  );
}