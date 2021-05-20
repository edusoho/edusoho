import { apiClient } from 'common/vue/service/api-client.js';

export const Teacher = {
  async search(params) {
    return apiClient.get('/api/teachers', params);
  },
}