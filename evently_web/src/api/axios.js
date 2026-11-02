import axios from 'axios';

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

// Interceptor to attach the Bearer token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export async function ensureCsrfCookie() {
  // Only needed if we rely on session cookies (local dev), but safe to keep
  await axios.get(`${baseURL}/sanctum/csrf-cookie`, { withCredentials: true });
}

export default api;