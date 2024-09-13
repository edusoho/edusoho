import {apiClient} from '../api-client';

export default {
  async upload(params) {
    return apiClient.post('/file', params);
  },
};
