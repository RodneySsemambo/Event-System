import api from './axios';

export function fetchFavorites() {
  return api.get('/api/favorites').then((res) => res.data);
}

export function toggleFavorite(providerId) {
  return api
    .post('/api/favorites/toggle', { provider_profile_id: providerId })
    .then((res) => res.data);
}