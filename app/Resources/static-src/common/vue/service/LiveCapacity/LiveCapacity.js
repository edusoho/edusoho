import { apiClient } from 'common/vue/service/api-client.js';

export const LiveCapacity = {
  async search(params) {
    return apiClient.get(`/api/live_capacity`, params)
  }
}