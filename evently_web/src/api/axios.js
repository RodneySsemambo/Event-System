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

// Attach the Bearer token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token && token !== 'undefined') {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Keep this export so AuthContext doesn't break
export async function ensureCsrfCookie() {
  await axios.get(`${baseURL}/sanctum/csrf-cookie`, { withCredentials: true });
}

export default api;