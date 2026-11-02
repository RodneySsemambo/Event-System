import { createContext, useCallback, useContext, useEffect, useState } from 'react';
import api from '../api/axios';

const AuthContext = createContext(null);

// Move interceptor OUTSIDE the component so it runs immediately
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token && token !== 'undefined') {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const refreshUser = useCallback(async () => {
    const res = await api.get('/api/me');
    setUser(res.data.user);
    return res.data.user;
  }, []);

  useEffect(() => {
    let cancelled = false;
    api.get('/api/me')
      .then((res) => {
        if (!cancelled) setUser(res.data.user);
      })
      .catch(() => {
        if (!cancelled) setUser(null);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => { cancelled = true; };
  }, []);

  const register = useCallback(async (payload) => {
    const res = await api.post('/api/register', payload);
    localStorage.setItem('auth_token', res.data.token); 
    setUser(res.data.user);
    return res.data.user;
  }, []);

  const login = useCallback(async (email, password) => {
    const res = await api.post('/api/login', { email, password });
    localStorage.setItem('auth_token', res.data.token); 
    setUser(res.data.user);
    return res.data.user;
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('/api/logout');
    } catch (err) {
      console.warn('Logout failed, forcing local logout', err.message);
    } finally {
      localStorage.removeItem('auth_token');
      setUser(null);
    }
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