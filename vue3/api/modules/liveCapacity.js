import {ajaxClient} from '../api-client';

export default {
  async get(url) {
    return ajaxClient.get(url);
  },
}