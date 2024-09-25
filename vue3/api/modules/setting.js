import {apiClient} from '../api-client';

export default {
  async get(name) {
    return apiClient.get(`/setting/${name}`);
  },
};
