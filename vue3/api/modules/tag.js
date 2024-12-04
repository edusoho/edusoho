import {apiClient} from '../api-client';

export default {
  async fetchReplayTag() {
    return apiClient.get(`/tag`);
  },
}