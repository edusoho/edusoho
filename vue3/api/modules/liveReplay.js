import {apiClient} from '../api-client';

export default {
  async searchLiveReplay(params) {
    return apiClient.get('/live_replay', {params});
  },
}