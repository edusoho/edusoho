import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api';

export const Coupon = {
  async get({ params }) {
    return apiClient.get(`${baseUrl}/coupon_batch`, { params });
  }
}