import { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import TurnstileWidget from '../components/TurnstileWidget';
import api from '../api/axios';
import { useAuth } from '../context/AuthContext';
import './Auth.css';

const initialForm = {
  name: '', email: '', password: '', password_confirmation: '',
  role: 'client', phone: '', default_location: '',
  business_name: '', category_id: '', location: '',
};

export default function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();

  const [form, setForm] = useState(initialForm);
  const [categories, setCategories] = useState([]);
  const [errors, setErrors] = useState({});
  const [submitting, setSubmitting] = useState(false);
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  const [turnstileToken, setTurnstileToken] = useState(null);

  useEffect(() => {
    api.get('/api/categories').then((res) => setCategories(res.data));
  }, []);

  function updateField(e) {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  }

   async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});

    if (!agreedToTerms || !turnstileToken) {
      setErrors({ general: ['Please complete the human verification and agree to the terms.'] });
      setSubmitting(false);
      return;
    }

    try {
      // Pass the extra fields required by your backend
      const payload = {
        ...form,
        agreed_to_terms: agreedToTerms,
        'cf-turnstile-response': turnstileToken,
      };
      
      await register(payload);
      navigate('/dashboard');
    } catch (err) {
      setErrors(
        err.response?.status === 422
          ? err.response.data.errors || {}
          : { general: ['Something went wrong. Please try again.'] }
      );
    } finally {
      setSubmitting(false);
    }
  }
  const fieldError = (name) => errors[name]?.[0];

  return (
    <div className="auth">
      <aside className="auth__brand">
        <Link to="/Home" className="auth__logo">Evently</Link>
        <div className="auth__brand-body">
          <Link to="/" className="auth__back" aria-label="Back to home">←</Link>
          <p className="auth__kicker">Get started</p>
          <h2 className="auth__headline">Plan your next event, or get booked for it.</h2>
          <p className="auth__sub">
            One account. Two sides of the market. Verified providers, real reviews, mobile money.
          </p>
        </div>
        <p className="auth__footer">Event services, found in minutes.</p>
      </aside>

      <main className="auth__main">
        <div className="auth__form-wrap">
          <h1 className="auth__title">Create an account</h1>
          <p className="auth__subtitle">It takes less than a minute.</p>

          {fieldError('general') && <div className="auth__error">{fieldError('general')}</div>}

          <form onSubmit={handleSubmit} className="auth__form">
            <div className="auth__field">
              <span className="auth__label">I am a</span>
              <div className="auth__role-group">
                <label className={`auth__role-option ${form.role === 'client' ? 'auth__role-option--active' : ''}`}>
                  <input
                    type="radio" name="role" value="client"
                    checked={form.role === 'client'} onChange={updateField}
                  />
                  <span>Client</span>
                </label>
                <label className={`auth__role-option ${form.role === 'provider' ? 'auth__role-option--active' : ''}`}>
                  <input
                    type="radio" name="role" value="provider"
                    checked={form.role === 'provider'} onChange={updateField}
                  />
                  <span>Provider</span>
                </label>
              </div>
            </div>

            <AuthField label="Full name" name="name" value={form.name} onChange={updateField} error={fieldError('name')} />
            <AuthField label="Email" name="email" type="email" value={form.email} onChange={updateField} error={fieldError('email')} />

            <div className="auth__row">
              <AuthField label="Password" name="password" type="password" value={form.password} onChange={updateField} error={fieldError('password')} />
              <AuthField label="Confirm password" name="password_confirmation" type="password" value={form.password_confirmation} onChange={updateField} />
            </div>

            {form.role === 'client' && (
              <>
                <AuthField label="Phone (optional)" name="phone" value={form.phone} onChange={updateField} error={fieldError('phone')} />
                <AuthField label="Default location (optional)" name="default_location" value={form.default_location} onChange={updateField} />
              </>
            )}

            {form.role === 'provider' && (
              <>
                <AuthField label="Business name" name="business_name" value={form.business_name} onChange={updateField} error={fieldError('business_name')} />

                <div className="auth__field">
                  <label className="auth__label" htmlFor="category_id">Category</label>
                  <select
                    id="category_id"
                    name="category_id"
                    value={form.category_id}
                    onChange={updateField}
                    className="auth__input"
                  >
                    <option value="">Select a category…</option>
                    {categories.map((c) => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                  {fieldError('category_id') && <p className="auth__field-error">{fieldError('category_id')}</p>}
                </div>

                <AuthField label="Location" name="location" value={form.location} onChange={updateField} error={fieldError('location')} />
              </>
            )}

            <label className="auth__checkbox">
  <input
    type="checkbox"
    checked={agreedToTerms}
    onChange={(e) => setAgreedToTerms(e.target.checked)}
  />
  <span>
    I agree to Evently's{' '}
    <Link to="/legal#terms" target="_blank" className="auth__checkbox-link">
      Terms of Service
    </Link>
    {' '}and{' '}
    <Link to="/legal#privacy" target="_blank" className="auth__checkbox-link">
      Privacy Policy
    </Link>
    .
              </span>
              <TurnstileWidget
  onVerify={(token) => setTurnstileToken(token)}
  onExpire={() => setTurnstileToken(null)}
/>
            </label>
            
           <button
  type="submit"
  className="auth__submit"
  disabled={submitting || !agreedToTerms || !turnstileToken}
>
  {submitting ? 'Creating account…' : 'Create account'}
</button>
          </form>

          <p className="auth__switch">
            Already have an account? <Link to="/login">Log in</Link>
          </p>
        </div>
      </main>
    </div>
  );
}

function AuthField({ label, name, value, onChange, type = 'text', error }) {
  return (
    <label className="auth__field">
      <span className="auth__label">{label}</span>
      <input
        type={type}
        name={name}
        value={value}
        onChange={onChange}
        className="auth__input"
      />
      {error && <p className="auth__field-error">{error}</p>}
    </label>
  );
}