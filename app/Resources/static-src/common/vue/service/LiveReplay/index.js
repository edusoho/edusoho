import { apiClient } from 'common/vue/service/api-client';

const baseUrl = '/api/live_replay';

export const LiveReplay = {
  async get() {
    return apiClient.get(`${baseUrl}`);
  }
}
