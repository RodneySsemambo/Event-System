import axios from 'axios';

// Use the environment variable in production, and localhost in development
const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

// Attach the Google OAuth bearer token to every request (if it exists)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// This is required by AuthContext.jsx for email/password login
export async function ensureCsrfCookie() {
  await axios.get(`${baseURL}/sanctum/csrf-cookie`, { withCredentials: true });
}

export default api;