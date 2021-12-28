import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/pages/apps';

export const Pages = {
  async appsSettings({ params, data }) {
    return apiClient.post(`${baseUrl}/settings`, data, { params });
  },

  async appsDiscovery({ params }) {
    return apiClient.get(`${baseUrl}/settings/discovery`, { params });
  }
}