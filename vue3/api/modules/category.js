import {ajaxClient} from '../api-client';

export default {
  async getCategory(type) {
    return ajaxClient.get(`/category/choices/${type}`);
  },
}