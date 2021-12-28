import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/category';

export const Category = {
  async get({ query }) {
    return apiClient.get(`${baseUrl}/${query.type}`);
  }
}