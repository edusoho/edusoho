import {ajaxClient} from '../api-client';

export default {
  async searchTags(params) {
    return ajaxClient.get('/tag/match_jsonp', {params});
  },
}