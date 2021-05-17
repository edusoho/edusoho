import { apiClient as client } from '../api-client.js';

export default {
  User: {
    async test(params) {
      return await client.get('/test', params);
    },

    async test1(params) {
      return await client.get('/test1', params);
    }
  }
}
