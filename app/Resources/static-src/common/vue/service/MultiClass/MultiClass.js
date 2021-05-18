import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/multi_class'

export const MultiClass = {
  async add(params) {
    return apiClient.post(baseUrl, params)
  }
}