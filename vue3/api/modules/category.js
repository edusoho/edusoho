import {ajaxClient} from '../api-client';

export default {
  async getCategory() {
    return ajaxClient.get('/category/choices/course');
  },
}