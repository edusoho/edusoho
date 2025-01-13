import {ajaxClient, apiClient} from '../api-client';

export default {
  async search() {
    return apiClient.get(`/org`);
  },
}