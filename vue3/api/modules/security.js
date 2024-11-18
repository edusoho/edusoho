import {ajaxClient} from '../api-client';

export default {
  async decryptMobile(encryptedMobile) {
    return ajaxClient.post('/show_mobile', new URLSearchParams({encryptedMobile}));
  },
};
