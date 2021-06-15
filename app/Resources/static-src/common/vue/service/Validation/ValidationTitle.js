import { apiClient } from 'common/vue/service/api-client.js';

export const ValidationTitle = {
  // 同名校验 
  async search(params) {
    return apiClient.get(`/api/validation/${params.type}/title`, { params })
  }
}
