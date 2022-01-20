import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/purchaseAgreement';

export const PurchaseAgreement = {
  async get() {
    return apiClient.get(`${baseUrl}`)
  },

  async update({ data }) {
    return apiClient.post(`${baseUrl}`, data)
  }
}