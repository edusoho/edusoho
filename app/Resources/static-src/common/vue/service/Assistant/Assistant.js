import { apiClient } from 'common/vue/service/api-client.js';

export const Assistant = {
  // 产品列表
  async search(multiClassId, params) {
    // return apiClient.get('/api/assistants', params);
    return apiClient.get(`/api/multi_class/${multiClassId}/students`);
  },
}