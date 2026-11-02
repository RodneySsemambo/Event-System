import axios from 'axios';

// Use the environment variable in production, and the local URL in development
const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

export async function ensureCsrfCookie() {
  // Also use the baseURL here so the CSRF cookie comes from Render, not Vercel
  await axios.get(`${baseURL}/sanctum/csrf-cookie`, { withCredentials: true });
}

export default api;