import { createContext, useCallback, useContext, useEffect, useState } from 'react';
import api, { ensureCsrfCookie } from '../api/axios';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true); // true while we check for an existing session

  const refreshUser = useCallback(async () => {
    const res = await api.get('/api/me');
    setUser(res.data.user);
    return res.data.user;
  }, []);

  // On first load, ask the API "who am I?" so a page refresh doesn't
  // lose the logged-in state (the session cookie is still valid).
  useEffect(() => {
    let cancelled = false;

    api
      .get('/api/me')
      .then((res) => {
        if (!cancelled) setUser(res.data.user);
      })
      .catch(() => {
        if (!cancelled) setUser(null);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  const register = useCallback(async (payload) => {
    await ensureCsrfCookie();
    const res = await api.post('/api/register', payload);
    setUser(res.data.user);
    return res.data.user;
  }, []);

 const login = useCallback(async (email, password) => {
  await ensureCsrfCookie();
  const res = await api.post('/api/login', {
    email,
    password,
    
  });
  setUser(res.data.user);
  return res.data.user;
}, []);

  const logout = useCallback(async () => {
    await api.post('/api/logout');
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, loading, register, login, logout, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside <AuthProvider>');
  return ctx;
}
