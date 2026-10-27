import axios from 'axios';

// In dev, Vite proxies /api and /sanctum to http://localhost:8000, so
// requests use relative URLs and are same-origin — cookies just work.
// In production (same domain), relative URLs also work.
const api = axios.create({
  baseURL: '', // relative — Vite proxy handles the rest
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

export async function ensureCsrfCookie() {
  await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export default api;