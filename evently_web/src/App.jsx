import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import ProviderProfileEdit from './pages/ProviderProfileEdit';
import Search from './pages/Search';
import ProviderPublicProfile from './pages/ProviderPublicProfile';
import MyBookings from "./pages/MyBookings";
import BookingRequestPage from './pages/BookingRequestPage';
import NotificationsPage from './pages/NotificationsPage';
import ProviderEarnings from './pages/ProviderEarnings';
import Legal from './pages/Legal';
import Favorites from './pages/Favorites';
export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          {/* Public */}
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/search" element={<Search />} />
          <Route path="/providers/:id" element={<ProviderPublicProfile />} />
          <Route path="/legal" element={<Legal />} /> 

          {/* Protected — any logged-in user */}
        <Route element={<ProtectedRoute />}>
  <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/bookings" element={<MyBookings />} />
            <Route path="/notifications" element={<NotificationsPage />} /> 
            <Route path="/book/:providerId" element={<BookingRequestPage />} />
            <Route path="/favorites" element={<Favorites />} />
</Route>

          {/* Protected — provider only */}
          <Route element={<ProtectedRoute roles={['provider']} />}>
            <Route path="/profile/edit" element={<ProviderProfileEdit />} />
  <Route path="/earnings" element={<ProviderEarnings />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}