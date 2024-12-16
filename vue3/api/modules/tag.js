import {ajaxClient} from '../api-client';

export default {
  async getTags(params) {
    return ajaxClient.get('/tag/match_jsonp', {params});
  },
  async fetchReplayTag() {
      return apiClient.get(`/tag`);
  }
}