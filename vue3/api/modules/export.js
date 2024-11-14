import {ajaxClient} from '../api-client';

export default {
  async try(name, params) {
    return ajaxClient.get(`/try/export/${name}`, {params});
  },
  async pre(name, params) {
    return ajaxClient.get(`/pre/export/${name}`, {params});
  },
};
