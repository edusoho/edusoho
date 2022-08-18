import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/latest_information';

export const Information = {
  async search(params) {
    return apiClient.get(`${baseUrl}`, { params })
  },
}
