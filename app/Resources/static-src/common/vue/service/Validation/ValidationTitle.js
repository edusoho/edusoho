import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/multi_class';

export const ValidationTitle = {
  // 同名校验 
  async search({ type, title }) {
    return apiClient.post(`/api/validation/${type}/title`, { title })
  }
}