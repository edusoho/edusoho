import { apiClient } from 'common/vue/service/api-client.js';

export const User = {
  async get(id) {
    return apiClient.get(`/api/user/${id}`);
  },
}
