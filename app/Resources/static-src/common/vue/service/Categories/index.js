import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/categories';

export const Categories = {
  async get({ query }) {
    return apiClient.get(`${baseUrl}/${query.type}`);
  }
}