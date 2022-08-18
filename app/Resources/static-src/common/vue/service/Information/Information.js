import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/latest_real_time_info';

export const Information = {
  async search(params) {
    return apiClient.get(`${baseUrl}`, { params })
  },
}
