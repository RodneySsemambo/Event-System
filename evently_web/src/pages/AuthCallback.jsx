import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function AuthCallback() {
  const navigate = useNavigate();
  const { refreshUser } = useAuth();

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (!token) {
      navigate('/login?error=google_failed');
      return;
    }

    // Store the token for API requests
    localStorage.setItem('auth_token', token);

    // Refresh user and go to dashboard
    refreshUser()
      .then(() => navigate('/dashboard'))
      .catch(() => navigate('/login?error=google_failed'));
  }, [navigate, refreshUser]);

  return <p style={{ padding: 40, textAlign: 'center' }}>Signing you in…</p>;
}