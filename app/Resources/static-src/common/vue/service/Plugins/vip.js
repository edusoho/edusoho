import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/plugins/vip';

export const Vip = {
  async getLevels() {
    return apiClient.get(`${baseUrl}/vip_levels`)
  }
}