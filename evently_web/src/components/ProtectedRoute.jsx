import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function ProtectedRoute({ roles }) {
  const { user, loading } = useAuth();

  if (loading) {
    return <div style={{ padding: 40, textAlign: 'center' }}>Loading…</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // If the logged-in user is an admin, send them to the admin panel
  // rather than showing a broken client/provider experience.
  if (user.role === 'admin' && !roles?.includes('admin')) {
    // Use a hard redirect so Filament's full page reload happens cleanly
    window.location.href = 'http://localhost:8000/admin';
    return null;
  }

  if (roles && !roles.includes(user.role)) {
    return <Navigate to="/dashboard" replace />;
  }

  return <Outlet />;
}