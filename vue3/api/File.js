import { apiClient } from 'common/vue/service/api-client';

export const FileApi = {
  async uploadFile(params) {
    return apiClient.post('/api/files', params);
  },
}
