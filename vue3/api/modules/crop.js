import {ajaxClient} from '../api-client';

export default {
  async crop(params) {
    return ajaxClient.post('/file/img/crop', params);
  },
};