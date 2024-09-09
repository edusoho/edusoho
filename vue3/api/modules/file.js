import {ajaxClient, apiClient} from '../api-client';

export default {
  async upload(params) {
    return apiClient.post('/file', params);
  },
  async getCourseCover(params) {
    return ajaxClient.get('/render/upload/image', {params});
  },
};
