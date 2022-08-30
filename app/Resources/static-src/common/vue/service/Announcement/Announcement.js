import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/latest_announcement';

export const Announcement = {
  async search(params) {
    return apiClient.get(`${baseUrl}/get`, { params })
  },
}
