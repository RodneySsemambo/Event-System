import api from './axios';

export function fetchNotifications() {
  return api.get('/api/notifications').then((res) => res.data);
}

export function markNotificationRead(id) {
  return api.patch(`/api/notifications/${id}/read`).then((res) => res.data);
}

export function markAllRead() {
  return api.post('/api/notifications/read-all').then((res) => res.data);
}