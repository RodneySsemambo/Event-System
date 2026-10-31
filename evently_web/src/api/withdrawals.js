import api from './axios';

export function fetchEarnings() {
  return api.get('/api/withdrawals').then((res) => res.data);
}

export function requestWithdrawal(amount, phoneNumber) {
  return api
    .post('/api/withdrawals', { amount, phone_number: phoneNumber })
    .then((res) => res.data);
}