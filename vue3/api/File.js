import { apiClient } from './api-client';

export const FileApi = {
  async uploadFile(params) {
    return apiClient.post('/api/files', params);
  },
}
