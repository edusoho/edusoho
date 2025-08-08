import {apiClient} from '../api-client';

export default {
  async search(params) {
    return apiClient.get('/teachers', {params});
  },
};