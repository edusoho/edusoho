import {ajaxClient} from '../api-client';

export default {
  async getOrgCodes(params) {
    return ajaxClient.get('/render/org', {params});
  },
}