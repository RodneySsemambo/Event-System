import api from './axios';

export function fetchProvider(id) {
  return api.get(`/api/providers/${id}`).then((res) => res.data);
}

export function fetchCategories() {
  return api.get('/api/categories').then((res) => res.data);
}

/**
 * Laravel doesn't parse multipart bodies on a real PUT request, so file
 * uploads use POST with a `_method=PUT` spoof field, which Laravel's
 * MethodField middleware reads and treats as a PUT for routing/auth.
 */
export function updateProviderProfile(id, fields, files) {
  const formData = new FormData();
  formData.append('_method', 'PUT');

  Object.entries(fields).forEach(([key, value]) => {
    if (value !== null && value !== undefined) formData.append(key, value);
  });

  if (files.profile_photo) formData.append('profile_photo', files.profile_photo);
  if (files.banner) formData.append('banner', files.banner);

  return api.post(`/api/providers/${id}`, formData).then((res) => res.data);
}

export function uploadPortfolioItem(providerId, file, caption) {
  const formData = new FormData();
  formData.append('image', file);
  if (caption) formData.append('caption', caption);

  return api.post(`/api/providers/${providerId}/portfolio`, formData).then((res) => res.data);
}

export function updatePortfolioItem(itemId, caption) {
  return api.patch(`/api/portfolio/${itemId}`, { caption }).then((res) => res.data);
}

export function deletePortfolioItem(itemId) {
  return api.delete(`/api/portfolio/${itemId}`);
}

export function reorderPortfolio(providerId, orderedIds) {
  return api
    .post(`/api/providers/${providerId}/portfolio/reorder`, { order: orderedIds })
    .then((res) => res.data);
}
