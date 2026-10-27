import api from './axios';

export function searchProviders(params) {
  return api.get('/api/search', { params }).then((res) => res.data);
}

export function createBooking(payload) {
  return api.post('/api/bookings', payload).then((res) => res.data);
}

export function fetchMyBookings(page = 1) {
  return api.get('/api/bookings', { params: { page } }).then((res) => res.data);
}

export function updateBookingStatus(bookingId, status, quotedAmount = null) {
  return api.patch(`/api/bookings/${bookingId}/status`, { status, quoted_amount: quotedAmount }).then((res) => res.data);
}

export function cancelBooking(bookingId) {
  return api.patch(`/api/bookings/${bookingId}/cancel`).then((res) => res.data);
}

export function completeBooking(bookingId) {
  return api.patch(`/api/bookings/${bookingId}/complete`).then((res) => res.data);
}

export function submitReview(bookingId, rating, comment) {
  return api.post(`/api/bookings/${bookingId}/review`, { rating, comment }).then((res) => res.data);
}
export function payBooking(bookingId, phoneNumber) {
  return api
    .post(`/api/bookings/${bookingId}/pay`, { phone_number: phoneNumber })
    .then((res) => res.data);
}
export function fetchPaymentStatus(bookingId) {
  return api.get(`/api/bookings/${bookingId}/payment`).then((res) => res.data);
}