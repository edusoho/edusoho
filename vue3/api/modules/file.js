import {apiClient} from '../api-client';

export default {
  async upload(params) {
    return apiClient.post('/file', params);
  },
  async getFileUsage(fileId, params) {
    return apiClient.get(`/upload_file/${fileId}/usage`, {params});
  },
  async searchUploadFile(params) {
    return apiClient.get('/upload_file', {params});
  },
  async replaceUploadFile(fileId, params) {
    return apiClient.post(`/upload_file/${fileId}/replace`, params);
  },
};
