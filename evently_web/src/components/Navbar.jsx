import { useEffect, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import NotificationBell from './NotificationBell';
import './Navbar.css';

export default function Navbar({ forceSolid = false }) {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [scrolled, setScrolled] = useState(forceSolid);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    if (forceSolid) return;
    function onScroll() {
      setScrolled(window.scrollY > 40);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, [forceSolid]);

  useEffect(() => {
    setMenuOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    document.body.style.overflow = menuOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [menuOpen]);

  const isProvider = user?.role === 'provider';

  async function handleLogout() {
    setMenuOpen(false);
    await logout();
  }

  return (
    <>
      <nav className={`nav ${scrolled ? 'nav--solid' : ''} ${menuOpen ? 'nav--open' : ''}`}>
        <div className="nav__inner">
          <Link to={user ? '/dashboard' : '/'} className="nav__logo">Evently</Link>

          <div className="nav__links">
            
            {user ? (
              <>
                <Link to="/search" className="nav__link">Find a provider</Link>
                {isProvider &&
                  <Link to="/profile/edit" className="nav__link">Your profile</Link>
                }
                {user.role === 'client' && (
  <Link to="/favorites" className="nav__link">Favorites</Link>
)}
                {isProvider && (
  <>
    <Link to="/earnings" className="nav__link">Earnings</Link>
  </>
)}
                <Link to="/bookings" className="nav__link">Bookings</Link>
                <Link to="/" className="nav__link">Home</Link>
              </>
            ) : (
              <>
                <Link to="/search" className="nav__link">Browse providers</Link>
                <a href="/#how-it-works" className="nav__link">How it works</a>
                <a href="/#testimonials" className="nav__link">Reviews</a>
              </>
            )}
          </div>

          <div className="nav__actions">
            {user ? (
              <>
                <NotificationBell />
                <Link to="/dashboard" className="nav__user" title="Dashboard">
                  Hi, {user.name.split(' ')[0]}
                </Link>
                <button className="nav__btn nav__btn--ghost" onClick={handleLogout}>Log out</button>
              </>
            ) : (
              <>
                <Link to="/login" className="nav__btn nav__btn--ghost">Log in</Link>
                <Link to="/register" className="nav__btn nav__btn--solid">Get started</Link>
              </>
            )}
          </div>

          <div className="nav__mobile-actions">
            {user && <NotificationBell />}
            <button
              type="button"
              className="nav__toggle"
              onClick={() => setMenuOpen((o) => !o)}
              aria-label={menuOpen ? 'Close menu' : 'Open menu'}
              aria-expanded={menuOpen}
            >
              <span className={`nav__toggle-bar ${menuOpen ? 'nav__toggle-bar--open-top' : ''}`} />
              <span className={`nav__toggle-bar ${menuOpen ? 'nav__toggle-bar--open-mid' : ''}`} />
              <span className={`nav__toggle-bar ${menuOpen ? 'nav__toggle-bar--open-bottom' : ''}`} />
            </button>
          </div>
        </div>
      </nav>

      {/* Drawer lives OUTSIDE the <nav> so backdrop-filter on the nav
          doesn't trap its fixed positioning. */}
      {menuOpen && (
  <>
    <div
      className="nav__backdrop"
      onClick={() => setMenuOpen(false)}
      aria-hidden="true"
    />
    <aside className="nav__drawer" role="dialog" aria-modal="true">
      <div className="nav__drawer-inner">
        <div className="nav__drawer-head">
          <span className="nav__drawer-logo">Evently</span>
          <button
            type="button"
            className="nav__drawer-close"
            onClick={() => setMenuOpen(false)}
            aria-label="Close menu"
          >
            ×
          </button>
        </div>

        {user ? (
          <>
            <p className="nav__drawer-label">Signed in as {user.name}</p>
            <Link to="/dashboard" className="nav__drawer-link">Dashboard</Link>
            <Link to="/search" className="nav__drawer-link">Find a provider</Link>
            {isProvider && (
                    <Link to="/profile/edit" className="nav__drawer-link">Your profile</Link>
                    
                  )}
                  {user.role === 'client' && (
  <Link to="/favorites" className="nav__drawer-link">Favorites</Link>
)}
                  {isProvider && (
  <>
    <Link to="/earnings" className="nav__drawer-link">Earnings</Link>
  </>
)}
            <Link to="/bookings" className="nav__drawer-link">Bookings</Link>
            <Link to="/" className="nav__drawer-link">Home</Link>
            <button
              type="button"
              className="nav__drawer-link nav__drawer-link--danger"
              onClick={handleLogout}
            >
              Log out
            </button>
          </>
        ) : (
          <>
            <Link to="/search" className="nav__drawer-link">Browse providers</Link>
            <a href="/#how-it-works" className="nav__drawer-link">How it works</a>
            <a href="/#testimonials" className="nav__drawer-link">Reviews</a>
            <div className="nav__drawer-actions">
              <Link to="/login" className="ui-btn ui-btn--ghost">Log in</Link>
              <Link to="/register" className="ui-btn ui-btn--primary">Get started</Link>
            </div>
          </>
        )}
      </div>
    </aside>
  </>
)}
    </>
  );
}