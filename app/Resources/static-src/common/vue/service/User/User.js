import { apiClient } from 'common/vue/service/api-client.js';

export const User = {
  async get(id) {
    return apiClient.get(`/api/user/${id}`);
  },

  async mdityDisplay({ query, params }) {
    return apiClient.patch(`/api/user/${query.id}`, params)
  }
}
