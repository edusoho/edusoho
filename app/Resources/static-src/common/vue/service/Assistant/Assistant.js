import { apiClient } from 'common/vue/service/api-client.js';

export const Assistant = {
  async search(params) {
    return apiClient.get('/api/assistants', params);
  },
}