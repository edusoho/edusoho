import {ajaxClient, apiClient} from '../api-client';

export default {
  async upload(params) {
    return apiClient.post('/file', params);
  },
  async getCourseCoverTemplate(params) {
    return ajaxClient.get('/render/upload/image', {params});
  },
  async getFileUsage(fileId, params) {
    return apiClient.get(`/upload_file/${fileId}/usage`, {params});
  },
};
