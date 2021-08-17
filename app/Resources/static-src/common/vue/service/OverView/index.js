import { apiClient } from 'common/vue/service/api-client.js';

const baseUrl = '/api/timeout_review';

export default {
  search ({ query = {}, params = {}, data = {} } = {}) {
    return apiClient.get(`${baseUrl}`, { params })
  }
}