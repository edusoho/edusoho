import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/timeout_review';

export default {
  async search ({ query, params, data } = {}) {
    return await apiClient.get(`${baseUrl}`, { params })
  }
}