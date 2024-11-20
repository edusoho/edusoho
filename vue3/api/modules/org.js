import {apiClient} from '../api-client';

export default {
  async search() {
    return apiClient.get(`/org`);
  },
}